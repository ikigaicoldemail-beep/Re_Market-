<?php

use App\Http\Controllers\Api\V1\ScheduledPostController;
use App\Http\Controllers\Api\V1\SocialAccountController;
use App\Http\Controllers\Api\V1\SocialPostController;
use App\Http\Controllers\SocialOAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:social')->group(function () {
    Route::post('/products/share', [SocialPostController::class, 'share'])->middleware('email.verified.marketplace');

    Route::get('/social/accounts', [SocialAccountController::class, 'index']);
    Route::post('/social/accounts', [SocialAccountController::class, 'store'])->middleware('email.verified.marketplace');
    Route::post('/social/{provider}/authorize', [SocialOAuthController::class, 'buildAuthorizeUrl'])
        ->where('provider', 'facebook');
    Route::delete('/social/accounts/{socialAccount}', [SocialAccountController::class, 'destroy'])->middleware('email.verified.marketplace');

    Route::get('/social/posts', [SocialPostController::class, 'index']);
    Route::post('/social/posts', [SocialPostController::class, 'store'])->middleware('email.verified.marketplace');
    Route::get('/social/posts/{socialPost}', [SocialPostController::class, 'show']);
    Route::post('/social/posts/{socialPost}/publish', [SocialPostController::class, 'publish'])->middleware('email.verified.marketplace');

    Route::get('/scheduled-posts', [ScheduledPostController::class, 'index']);
    Route::post('/scheduled-posts', [ScheduledPostController::class, 'store'])->middleware('email.verified.marketplace');
    Route::get('/scheduled-posts/{scheduledPost}', [ScheduledPostController::class, 'show']);
    Route::put('/scheduled-posts/{scheduledPost}', [ScheduledPostController::class, 'update'])->middleware('email.verified.marketplace');
    Route::delete('/scheduled-posts/{scheduledPost}', [ScheduledPostController::class, 'destroy'])->middleware('email.verified.marketplace');
});
