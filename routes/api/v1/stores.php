<?php

use App\Http\Controllers\Api\V1\StoreController;
use App\Http\Controllers\Api\V1\StoreFollowController;
use Illuminate\Support\Facades\Route;

Route::get('/me/store', [StoreController::class, 'myStore']);
Route::post('/stores', [StoreController::class, 'store']);
Route::get('/stores/{store}', [StoreController::class, 'show']);
Route::put('/stores/{store}', [StoreController::class, 'update']);
// Alias for multipart/form-data uploads (PHP cannot parse PUT multipart bodies).
Route::post('/stores/{store}', [StoreController::class, 'update']);

Route::post('/stores/{store}/follow', [StoreFollowController::class, 'follow']);
Route::delete('/stores/{store}/follow', [StoreFollowController::class, 'unfollow']);
Route::get('/me/following-stores', [StoreFollowController::class, 'followingStores']);
