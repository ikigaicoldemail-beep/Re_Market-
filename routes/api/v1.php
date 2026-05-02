<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AiSimilaritySearchController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProductController as V1ProductController;
use App\Http\Controllers\Api\V1\ScheduledPostController;
use App\Http\Controllers\Api\V1\SocialAccountController;
use App\Http\Controllers\Api\V1\SocialPostController;
use App\Http\Controllers\Api\V1\StoreController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'message' => 'Marketplace API is healthy.',
        'version' => 'v1',
    ]);
});

Route::middleware('throttle:auth')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('throttle:public-search')->group(function () {
    Route::get('/products', [V1ProductController::class, 'index']);
    Route::get('/products/{product}', [V1ProductController::class, 'show']);
    Route::get('/products/{product}/share', [V1ProductController::class, 'share']);
    Route::get('/stores/{store}/products', [V1ProductController::class, 'storePage']);
});

Route::middleware(['auth:api', 'activity.log'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [ProfileController::class, 'show']);
    Route::put('/me', [ProfileController::class, 'update']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/stores', [StoreController::class, 'adminIndex']);
    });

    Route::post('/stores', [StoreController::class, 'store']);
    Route::put('/stores/{store}', [StoreController::class, 'update']);

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy']);

    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'store']);
    Route::put('/cart/items/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::get('/orders/{order}/payment-status', [PaymentController::class, 'show']);

    Route::get('/me/products', [V1ProductController::class, 'myProducts']);
    Route::post('/products', [V1ProductController::class, 'store']);
    Route::put('/products/{product}', [V1ProductController::class, 'update']);
    Route::delete('/products/{product}', [V1ProductController::class, 'destroy']);
    Route::post('/products/{product}/images', [V1ProductController::class, 'uploadImages']);
    Route::middleware('throttle:ai')->group(function () {
        Route::post('/ai/similarity-search', [AiSimilaritySearchController::class, 'store']);
    });

    Route::middleware('throttle:social')->group(function () {
        Route::post('/products/share', [SocialPostController::class, 'share']);

        Route::get('/social/accounts', [SocialAccountController::class, 'index']);
        Route::post('/social/accounts', [SocialAccountController::class, 'store']);
        Route::delete('/social/accounts/{socialAccount}', [SocialAccountController::class, 'destroy']);

        Route::get('/social/posts', [SocialPostController::class, 'index']);
        Route::post('/social/posts', [SocialPostController::class, 'store']);
        Route::get('/social/posts/{socialPost}', [SocialPostController::class, 'show']);
        Route::post('/social/posts/{socialPost}/publish', [SocialPostController::class, 'publish']);

        Route::get('/scheduled-posts', [ScheduledPostController::class, 'index']);
        Route::post('/scheduled-posts', [ScheduledPostController::class, 'store']);
        Route::get('/scheduled-posts/{scheduledPost}', [ScheduledPostController::class, 'show']);
        Route::put('/scheduled-posts/{scheduledPost}', [ScheduledPostController::class, 'update']);
        Route::delete('/scheduled-posts/{scheduledPost}', [ScheduledPostController::class, 'destroy']);
    });

    Route::middleware('throttle:chat')->group(function () {
        Route::get('/conversations', [ConversationController::class, 'index']);
        Route::post('/conversations', [ConversationController::class, 'store']);
        Route::get('/conversations/unread-count', [ConversationController::class, 'unreadCount']);
        Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'messages']);
        Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'send']);
        Route::post('/conversations/{conversation}/seen', [ConversationController::class, 'markAsSeen']);
    });
});
