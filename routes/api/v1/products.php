<?php

use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductReportController;
use Illuminate\Support\Facades\Route;

Route::get('/me/products', [ProductController::class, 'myProducts']);
Route::post('/products', [ProductController::class, 'store']);
Route::post('/products/schedule-post', [ProductController::class, 'schedulePost']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);
Route::post('/products/{product}/images', [ProductController::class, 'uploadImages']);
Route::post('/products/{product}/schedule-post', [ProductController::class, 'schedulePost']);

Route::get('/product-reports/reasons', [ProductReportController::class, 'reasons']);
Route::post('/products/{product}/report', [ProductReportController::class, 'store'])
    ->middleware('throttle:report-submit');

// Reviews
Route::post('/products/{product}/reviews', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'store']);
Route::put('/reviews/{review}', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'update']);
Route::delete('/reviews/{review}', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'destroy']);
Route::post('/reviews/{review}/reply', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'reply']);
Route::get('/me/reviews', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'myReviews']);
Route::get('/me/products/reviews', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'reviewsOnMyProducts']);
