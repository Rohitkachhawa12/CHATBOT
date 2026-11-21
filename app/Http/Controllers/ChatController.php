<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Show the main new chat screen.
     */
    public function index()
    {
        $groupedConversations = $this->getUserConversationsGrouped();

        return view('chatbot', [
            'groupedConversations' => $groupedConversations,
            'activeConversation'   => null,
            'messages'             => collect([]),
            'currentModel'         => env('OLLAMA_MODEL', 'qwen2.5:0.5b'),
        ]);
    }

    /**
     * Display a specific conversation by token with its full history.
     */
    public function showConversation(string $token)
    {
        $conversation = Conversation::where('conversation_token', $token)
            ->where('user_id', Auth::id())
            ->first();

        if (!$conversation) {
            return redirect('/')->with('error', 'Conversation not found.');
        }

        $messages = Message::where('conversation_id', $conversation->conversation_id)
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedConversations = $this->getUserConversationsGrouped();

        return view('chatbot', [
            'groupedConversations' => $groupedConversations,
            'activeConversation'   => $conversation,
            'messages'             => $messages,
            'currentModel'         => env('OLLAMA_MODEL', 'qwen2.5:0.5b'),
        ]);
    }

    /**
     * API: Get conversation list for the authenticated user.
     */
    public function getConversations()
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->latest('updated_at')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $conversations,
        ]);
    }

    /**
     * API: Get messages for a given conversation token.
     */
    public function getMessages(string $token)
    {
        $conversation = Conversation::where('conversation_token', $token)
            ->where('user_id', Auth::id())
            ->first();

        if (!$conversation) {
            return response()->json([
                'status'  => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        $messages = Message::where('conversation_id', $conversation->conversation_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status'       => true,
            'conversation' => $conversation,
            'data'         => $messages,
        ]);
    }

    /**
     * API: Send message to Ollama and store conversation & messages.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message'            => 'required|string',
            'conversation_token' => 'nullable|string',
        ]);

        $user = Auth::user();
        $token = $request->input('conversation_token');
        $isNewConversation = false;

        if ($token) {
            $conversation = Conversation::where('conversation_token', $token)
                ->where('user_id', $user->id)
                ->first();
        } else {
            $conversation = null;
        }

        if (!$conversation) {
            $isNewConversation = true;
            $cleanTitle = trim(preg_replace('/\s+/', ' ', $request->message));
            $conversation = Conversation::create([
                'user_id'            => $user->id,
                'conversation_token' => (string) Str::uuid(),
                'title'              => Str::limit($cleanTitle, 42),
            ]);
        }

        // Save user message
        $userMsg = Message::create([
            'conversation_id' => $conversation->conversation_id,
            'role'            => 'user',
            'content'         => $request->message,
        ]);

        $conversation->touch();

        // Retrieve past messages for multi-turn context
        $pastMessages = Message::where('conversation_id', $conversation->conversation_id)
            ->orderBy('created_at', 'asc')
            ->take(30)
            ->get();

        $ollamaMessages = [
            [
                'role'    => 'system',
                'content' => 'You are an intelligent, helpful AI assistant. Answer clearly, concisely, and use Markdown for formatting when useful.'
            ]
        ];

        foreach ($pastMessages as $msg) {
            $ollamaMessages[] = [
                'role'    => $msg->role,
                'content' => $msg->content,
            ];
        }

        // Ollama connection settings
        $baseUrl = rtrim(env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'), '/');
        $model = env('OLLAMA_MODEL', 'qwen2.5:0.5b');

        $reply = '';

        try {
            // Check available models in Ollama
            $tagResponse = Http::timeout(5)->get("{$baseUrl}/api/tags");
            if ($tagResponse->successful()) {
                $availableModels = collect($tagResponse->json()['models'] ?? [])->pluck('name')->toArray();
                if (!empty($availableModels)) {
                    $matched = false;
                    foreach ($availableModels as $m) {
                        if (str_starts_with($m, $model) || $m === $model) {
                            $model = $m;
                            $matched = true;
                            break;
                        }
                    }
                    if (!$matched) {
                        $model = $availableModels[0];
                    }
                }
            }

            // Call Ollama chat API
            $response = Http::timeout(180)->post("{$baseUrl}/api/chat", [
                'model'    => $model,
                'messages' => $ollamaMessages,
                'stream'   => false,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['message']['content'] ?? ($data['response'] ?? 'No response received from Ollama.');
            } else {
                $errorJson = $response->json();
                $errorMsg = $errorJson['error'] ?? ('HTTP status ' . $response->status());
                if (str_contains(strtolower($errorMsg), 'not found')) {
                    $reply = "Ollama model `{$model}` was not found. Please install a model by running `ollama run llama3` or `ollama run {$model}` in your terminal.";
                } else {
                    $reply = "Ollama error: " . $errorMsg;
                }
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $reply = "Could not connect to Ollama at `{$baseUrl}`. Please make sure Ollama is running (`ollama serve`).";
        } catch (\Throwable $e) {
            $reply = "Error communicating with Ollama: " . $e->getMessage();
        }

        // Save assistant reply
        $assistantMsg = Message::create([
            'conversation_id' => $conversation->conversation_id,
            'role'            => 'assistant',
            'content'         => $reply,
        ]);

        return response()->json([
            'status'              => true,
            'reply'               => $reply,
            'conversation'        => [
                'conversation_id'    => $conversation->conversation_id,
                'conversation_token' => $conversation->conversation_token,
                'title'              => $conversation->title,
                'created_at_human'   => $conversation->created_at->diffForHumans(),
            ],
            'user_message'        => $userMsg,
            'assistant_message'   => $assistantMsg,
            'is_new_conversation' => $isNewConversation,
            'model_used'          => $model,
        ]);
    }

    /**
     * API: Delete conversation.
     */
    public function deleteConversation(string $token)
    {
        $conversation = Conversation::where('conversation_token', $token)
            ->where('user_id', Auth::id())
            ->first();

        if (!$conversation) {
            return response()->json([
                'status'  => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        $conversation->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Conversation deleted successfully.',
        ]);
    }

    /**
     * API: Clear all conversations for the user.
     */
    public function clearAllConversations()
    {
        Conversation::where('user_id', Auth::id())->delete();

        return response()->json([
            'status'  => true,
            'message' => 'All conversations cleared successfully.',
        ]);
    }

    /**
     * Helper: group conversations by time intervals.
     */
    private function getUserConversationsGrouped(): array
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->latest('updated_at')
            ->get();

        $grouped = [
            'Today'           => [],
            'Yesterday'       => [],
            'Previous 7 Days' => [],
            'Previous 30 Days'=> [],
            'Older'           => [],
        ];

        $now = Carbon::now();

        foreach ($conversations as $c) {
            $date = Carbon::parse($c->updated_at ?? $c->created_at);

            if ($date->isToday()) {
                $grouped['Today'][] = $c;
            } elseif ($date->isYesterday()) {
                $grouped['Yesterday'][] = $c;
            } elseif ($date->greaterThanOrEqualTo($now->copy()->subDays(7))) {
                $grouped['Previous 7 Days'][] = $c;
            } elseif ($date->greaterThanOrEqualTo($now->copy()->subDays(30))) {
                $grouped['Previous 30 Days'][] = $c;
            } else {
                $grouped['Older'][] = $c;
            }
        }

        // Filter out empty groups
        return array_filter($grouped, fn($items) => !empty($items));
    }
}
