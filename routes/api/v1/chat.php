<?php

use App\Http\Controllers\Api\V1\ConversationController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:chat')->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/unread-count', [ConversationController::class, 'unreadCount']);
    Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'messages']);
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'send']);
    Route::post('/conversations/{conversation}/seen', [ConversationController::class, 'markAsSeen']);
});
