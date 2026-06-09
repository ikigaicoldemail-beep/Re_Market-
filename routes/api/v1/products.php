<?php

use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductReportController;
use Illuminate\Support\Facades\Route;

Route::get('/me/products', [ProductController::class, 'myProducts']);
Route::post('/products', [ProductController::class, 'store'])->middleware('email.verified.marketplace');
Route::post('/products/schedule-post', [ProductController::class, 'schedulePost'])->middleware('email.verified.marketplace');
Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('email.verified.marketplace');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('email.verified.marketplace');
Route::post('/products/{product}/images', [ProductController::class, 'uploadImages'])->middleware('email.verified.marketplace');
Route::post('/products/{product}/schedule-post', [ProductController::class, 'schedulePost'])->middleware('email.verified.marketplace');

Route::get('/products/{product}/review-eligibility', [ProductController::class, 'reviewEligibility']);

Route::get('/product-reports/reasons', [ProductReportController::class, 'reasons']);
Route::post('/products/{product}/report', [ProductReportController::class, 'store'])
    ->middleware('email.verified.marketplace')
    ->middleware('throttle:report-submit');

// Reviews
Route::post('/products/{product}/reviews', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'store'])->middleware('email.verified.marketplace');
Route::put('/reviews/{review}', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'update'])->middleware('email.verified.marketplace');
Route::delete('/reviews/{review}', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'destroy'])->middleware('email.verified.marketplace');
Route::post('/reviews/{review}/reply', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'reply'])->middleware('email.verified.marketplace');
Route::get('/me/reviews', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'myReviews']);
Route::get('/me/products/reviews', [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'reviewsOnMyProducts']);
