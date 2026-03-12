<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\MessageController;

Route::get('/share/product/{id}',[ProductController::class,'share']);
Route::get('/seller/dashboard/{id}', [ProductController::class, 'dashboard']);

Route::get('/seller/{id}',[ProductController::class,'seller']);
Route::get('/chat/{product_id}',[MessageController::class,'chat']);
Route::post('/message',[MessageController::class,'send']);
Route::delete('/product/{id}',[ProductController::class,'destroy']);
Route::put('/product/{id}',[ProductController::class,'update']);
Route::get('/product/{id}',[ProductController::class,'show']);
Route::get('/products',[ProductController::class,'index']);
Route::post('/product',[ProductController::class,'store']);
Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);

// Route::middleware('auth:api')->group(function(){

// Route::post('/product',[ProductController::class,'store']);

// Route::put('/product/{id}',[ProductController::class,'update']);

// Route::delete('/product/{id}',[ProductController::class,'destroy']);

// Route::post('/message',[MessageController::class,'send']);

// });
Route::get('/test', function () {
    return "API OK";
});