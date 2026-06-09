<?php

use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/cart', [CartController::class, 'show']);
Route::post('/cart/items', [CartController::class, 'store'])->middleware('email.verified.marketplace');
Route::put('/cart/items/{cartItem}', [CartController::class, 'update'])->middleware('email.verified.marketplace');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy']);
Route::delete('/cart', [CartController::class, 'clear']);

Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('email.verified.marketplace');
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{order}', [OrderController::class, 'show']);
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->middleware('email.verified.marketplace');
Route::get('/orders/{order}/payment-status', [PaymentController::class, 'show']);
