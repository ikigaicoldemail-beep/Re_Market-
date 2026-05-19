<?php

use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductReviewController;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductConditionResource;
use App\Models\Category;
use App\Models\ProductCondition;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:public-search')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/{product}/share', [ProductController::class, 'share']);
    Route::get('/stores/{store}/products', [ProductController::class, 'storePage']);

    Route::get('/categories', function () {
        return response()->json([
            'categories' => CategoryResource::collection(Category::orderBy('name')->get()),
        ]);
    });

    Route::get('/product-conditions', function () {
        return response()->json([
            'product_conditions' => ProductConditionResource::collection(ProductCondition::orderBy('rank')->get()),
        ]);
    });

    Route::get('/products/{product}/reviews', [ProductReviewController::class, 'index']);
});
