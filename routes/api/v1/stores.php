<?php

use App\Http\Controllers\Api\V1\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/me/store', [StoreController::class, 'myStore']);
Route::post('/stores', [StoreController::class, 'store']);
Route::get('/stores/{store}', [StoreController::class, 'show']);
Route::put('/stores/{store}', [StoreController::class, 'update']);
