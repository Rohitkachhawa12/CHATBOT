<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AuthController;

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Chatbot views
    Route::get('/', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/c/{token}', [ChatController::class, 'showConversation'])->name('chat.show');
    Route::get('/chat', [ChatController::class, 'index']);

    // Profile and account actions
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/clear-all-conversations', [ChatController::class, 'clearAllConversations']);

    // Chat API actions
    Route::post('/send-message', [ChatController::class, 'sendMessage']);
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::get('/conversations/{token}/messages', [ChatController::class, 'getMessages']);
    Route::delete('/conversations/{token}', [ChatController::class, 'deleteConversation']);
});
