<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'message' => 'Marketplace API is healthy.',
        'version' => 'v1',
    ]);
});

require __DIR__.'/v1/auth.php';
require __DIR__.'/v1/public.php';

Route::middleware(['auth:api', 'account.active', 'activity.log'])->group(function () {
    require __DIR__.'/v1/admin.php';
    require __DIR__.'/v1/profile.php';
    require __DIR__.'/v1/stores.php';
    require __DIR__.'/v1/addresses.php';
    require __DIR__.'/v1/wishlist.php';
    require __DIR__.'/v1/commerce.php';
    require __DIR__.'/v1/products.php';
    require __DIR__.'/v1/social.php';
    require __DIR__.'/v1/chat.php';
});
