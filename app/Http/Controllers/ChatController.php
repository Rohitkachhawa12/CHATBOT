<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('chatbot');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function chat(Request $request)
    {
        $response = Http::timeout(300)
            ->post('http://localhost:11434/api/generate', [
                'model' => 'qwen3',
                'prompt' => $request->message,
                'stream' => false,
            ]);

        return response()->json([
            'reply' => $response->json()['response']
        ]);
    }

    public function createConversation(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $conversation = Conversation::create([
            'user_id'            => auth()->id() ?? null,
            'conversation_token' => Str::uuid(),
            'title'              => Str::limit($request->message, 100),
        ]);

        return response()->json([
            'status' => true,
            'conversation' => [
                'conversation_id'    => $conversation->conversation_id,
                'conversation_token' => $conversation->conversation_token,
                'title'              => $conversation->title,
            ]
        ]);
    }

    public function getConversations()
    {
        // Conversation list
        $conversations = Conversation::latest()->get();

        return response()->json([
            'status' => true,
            'data' => $conversations
        ]);
    }

    public function getMessages($id)
    {
        // Messages load
        $messages = Message::where(
            'conversation_id',
            $id
        )->get();

        return response()->json([
            'status' => true,
            'data' => $messages
        ]);
    }

    public function sendMessage(Request $request)
    {
        // validation

        // save user message

        // get history

        // call ollama

        // save ai response

        // return response
    }
}
