<?php

use App\Http\Controllers\Api\V1\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:admin')->prefix('admin')->group(function () {
    Route::get('/stats', [AdminController::class, 'stats']);

    Route::get('/users', [AdminController::class, 'users']);
    Route::patch('/users/{user}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);

    Route::get('/stores', [AdminController::class, 'stores']);
    Route::patch('/stores/{store}', [AdminController::class, 'updateStore']);
    Route::delete('/stores/{store}', [AdminController::class, 'deleteStore']);

    Route::get('/products', [AdminController::class, 'products']);
    Route::patch('/products/{product}', [AdminController::class, 'updateProduct']);
    Route::delete('/products/{product}', [AdminController::class, 'deleteProduct']);

    Route::get('/categories', [AdminController::class, 'categories']);
    Route::post('/categories', [AdminController::class, 'storeCategory']);
    Route::post('/categories/{category}', [AdminController::class, 'updateCategory']);
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory']);

    Route::get('/brands', [AdminController::class, 'brands']);
    Route::post('/brands', [AdminController::class, 'storeBrand']);
    Route::post('/brands/{brand}', [AdminController::class, 'updateBrand']);
    Route::delete('/brands/{brand}', [AdminController::class, 'destroyBrand']);

    Route::get('/promo-banners', [AdminController::class, 'promoBanners']);
    Route::post('/promo-banners', [AdminController::class, 'storePromoBanner']);
    Route::post('/promo-banners/{promoBanner}', [AdminController::class, 'updatePromoBanner']);
    Route::delete('/promo-banners/{promoBanner}', [AdminController::class, 'destroyPromoBanner']);

    Route::post('/products/{product}/feature', [AdminController::class, 'toggleProductFeatured']);

    Route::get('/reports', [AdminController::class, 'reports']);
    Route::patch('/reports/{report}', [AdminController::class, 'updateReport']);
});
