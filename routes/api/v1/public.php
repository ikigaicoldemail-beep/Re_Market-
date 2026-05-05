<?php

use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:public-search')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/{product}/share', [ProductController::class, 'share']);
    Route::get('/stores/{store}/products', [ProductController::class, 'storePage']);
});
