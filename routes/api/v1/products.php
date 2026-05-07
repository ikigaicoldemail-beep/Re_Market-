<?php

use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/me/products', [ProductController::class, 'myProducts']);
Route::post('/products', [ProductController::class, 'store']);
Route::post('/products/schedule-post', [ProductController::class, 'schedulePost']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);
Route::post('/products/{product}/images', [ProductController::class, 'uploadImages']);
Route::post('/products/{product}/schedule-post', [ProductController::class, 'schedulePost']);
