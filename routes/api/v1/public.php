<?php

use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductReviewController;
use App\Http\Controllers\Api\V1\StoreController;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductConditionResource;
use App\Http\Resources\PromoBannerResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductCondition;
use App\Models\PromoBanner;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:public-search')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/{product}/share', [ProductController::class, 'share']);
    Route::get('/stores', [StoreController::class, 'publicIndex']);
    Route::get('/stores/{store}/products', [ProductController::class, 'storePage']);

    Route::get('/categories', function () {
        return response()->json([
            'categories' => CategoryResource::collection(Category::orderBy('name')->get()),
        ]);
    });

    Route::get('/brands', function () {
        return response()->json([
            'brands' => BrandResource::collection(
                Brand::withCount(['products' => fn ($q) => $q->where('status', 'published')->where('visibility', 'public')])
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
            ),
        ]);
    });

    Route::get('/promo-banners', function () {
        $now = now();
        return response()->json([
            'promo_banners' => PromoBannerResource::collection(
                PromoBanner::where('status', 'active')
                    ->where(function ($q) use ($now) {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                    })
                    ->orderBy('sort_order')
                    ->latest('id')
                    ->get()
            ),
        ]);
    });

    Route::get('/product-conditions', function () {
        return response()->json([
            'product_conditions' => ProductConditionResource::collection(ProductCondition::orderBy('rank')->get()),
        ]);
    });

    Route::get('/products/{product}/reviews', [ProductReviewController::class, 'index']);
});
