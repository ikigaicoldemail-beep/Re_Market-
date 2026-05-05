<?php

use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/cart', [CartController::class, 'show']);
Route::post('/cart/items', [CartController::class, 'store']);
Route::put('/cart/items/{cartItem}', [CartController::class, 'update']);
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy']);
Route::delete('/cart', [CartController::class, 'clear']);

Route::post('/checkout', [CheckoutController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{order}', [OrderController::class, 'show']);
Route::get('/orders/{order}/payment-status', [PaymentController::class, 'show']);
