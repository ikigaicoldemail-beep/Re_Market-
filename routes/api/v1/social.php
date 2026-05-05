<?php

use App\Http\Controllers\Api\V1\ScheduledPostController;
use App\Http\Controllers\Api\V1\SocialAccountController;
use App\Http\Controllers\Api\V1\SocialPostController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:social')->group(function () {
    Route::post('/products/share', [SocialPostController::class, 'share']);

    Route::get('/social/accounts', [SocialAccountController::class, 'index']);
    Route::post('/social/accounts', [SocialAccountController::class, 'store']);
    Route::delete('/social/accounts/{socialAccount}', [SocialAccountController::class, 'destroy']);

    Route::get('/social/posts', [SocialPostController::class, 'index']);
    Route::post('/social/posts', [SocialPostController::class, 'store']);
    Route::get('/social/posts/{socialPost}', [SocialPostController::class, 'show']);
    Route::post('/social/posts/{socialPost}/publish', [SocialPostController::class, 'publish']);

    Route::get('/scheduled-posts', [ScheduledPostController::class, 'index']);
    Route::post('/scheduled-posts', [ScheduledPostController::class, 'store']);
    Route::get('/scheduled-posts/{scheduledPost}', [ScheduledPostController::class, 'show']);
    Route::put('/scheduled-posts/{scheduledPost}', [ScheduledPostController::class, 'update']);
    Route::delete('/scheduled-posts/{scheduledPost}', [ScheduledPostController::class, 'destroy']);
});
