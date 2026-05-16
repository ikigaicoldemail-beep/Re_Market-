<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/me', [ProfileController::class, 'show']);
Route::put('/me', [ProfileController::class, 'update']);
Route::post('/me/avatar', [ProfileController::class, 'uploadAvatar']);
Route::post('/me/cover', [ProfileController::class, 'uploadCover']);
