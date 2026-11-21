<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_cannot_send_message(): void
    {
        $response = $this->postJson('/send-message', [
            'message' => 'Hello',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_send_message_and_create_conversation(): void
    {
        Http::fake([
            '*/api/tags' => Http::response(['models' => [['name' => 'qwen3:latest']]], 200),
            '*/api/chat' => Http::response([
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Hello from Ollama mock!'
                ]
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/send-message', [
            'message' => 'What is Laravel?',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('reply', 'Hello from Ollama mock!');
        $response->assertJsonPath('is_new_conversation', true);

        $this->assertDatabaseHas('conversations', [
            'user_id' => $user->id,
        ]);

        $conversation = Conversation::where('user_id', $user->id)->first();
        $this->assertNotNull($conversation);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->conversation_id,
            'role' => 'user',
            'content' => 'What is Laravel?',
        ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->conversation_id,
            'role' => 'assistant',
            'content' => 'Hello from Ollama mock!',
        ]);
    }

    public function test_user_can_view_conversation_by_token(): void
    {
        $user = User::factory()->create();
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'conversation_token' => (string) Str::uuid(),
            'title' => 'Test Chat',
        ]);

        Message::create([
            'conversation_id' => $conversation->conversation_id,
            'role' => 'user',
            'content' => 'Initial query',
        ]);

        $response = $this->actingAs($user)->get('/c/' . $conversation->conversation_token);
        $response->assertStatus(200);
        $response->assertSee('Initial query');
    }

    public function test_user_cannot_view_other_users_conversation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::create([
            'user_id' => $user1->id,
            'conversation_token' => (string) Str::uuid(),
            'title' => 'User1 Secret Chat',
        ]);

        // User 2 trying to access User 1's conversation
        $response = $this->actingAs($user2)->get('/c/' . $conversation->conversation_token);
        $response->assertRedirect('/');
    }

    public function test_user_can_delete_conversation(): void
    {
        $user = User::factory()->create();
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'conversation_token' => (string) Str::uuid(),
            'title' => 'Chat to delete',
        ]);

        $response = $this->actingAs($user)->deleteJson('/conversations/' . $conversation->conversation_token);
        $response->assertStatus(200);
        $response->assertJsonPath('status', true);

        $this->assertDatabaseMissing('conversations', [
            'conversation_id' => $conversation->conversation_id,
        ]);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)->postJson('/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', true);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_user_can_clear_all_conversations(): void
    {
        $user = User::factory()->create();
        Conversation::create([
            'user_id' => $user->id,
            'conversation_token' => (string) Str::uuid(),
            'title' => 'Chat 1',
        ]);
        Conversation::create([
            'user_id' => $user->id,
            'conversation_token' => (string) Str::uuid(),
            'title' => 'Chat 2',
        ]);

        $response = $this->actingAs($user)->postJson('/clear-all-conversations');
        $response->assertStatus(200);
        $response->assertJsonPath('status', true);

        $this->assertEquals(0, Conversation::where('user_id', $user->id)->count());
    }
}
