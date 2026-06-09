<?php

use App\Http\Controllers\Api\V1\StoreController;
use App\Http\Controllers\Api\V1\StoreFollowController;
use Illuminate\Support\Facades\Route;

Route::get('/me/store', [StoreController::class, 'myStore']);
Route::post('/stores', [StoreController::class, 'store'])->middleware('email.verified.marketplace');
Route::get('/stores/{store}', [StoreController::class, 'show']);
Route::put('/stores/{store}', [StoreController::class, 'update'])->middleware('email.verified.marketplace');
// Alias for multipart/form-data uploads (PHP cannot parse PUT multipart bodies).
Route::post('/stores/{store}', [StoreController::class, 'update'])->middleware('email.verified.marketplace');

Route::post('/stores/{store}/follow', [StoreFollowController::class, 'follow'])->middleware('email.verified.marketplace');
Route::delete('/stores/{store}/follow', [StoreFollowController::class, 'unfollow']);
Route::get('/me/following-stores', [StoreFollowController::class, 'followingStores']);
