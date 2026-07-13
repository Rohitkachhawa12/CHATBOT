<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('chatbot');
});

Route::resource('chats', ChatController::class);
Route::get('/chat', [ChatController::class, 'index']);
Route::post('/send-message', [ChatController::class, 'sendMessage']);
Route::post('/conversations', [ChatController::class, 'createConversation']);
Route::get('/conversations', [ChatController::class, 'getConversations']);
Route::get('/messages/{id}', [ChatController::class, 'getMessages']);
Route::post('/send-message', [ChatController::class, 'sendMessage']);
