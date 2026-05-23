<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/me', [ProfileController::class, 'show']);
Route::put('/me', [ProfileController::class, 'update']);
Route::post('/me/avatar', [ProfileController::class, 'uploadAvatar']);
Route::post('/me/cover', [ProfileController::class, 'uploadCover']);

Route::get('/notifications', [NotificationController::class, 'index']);
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
