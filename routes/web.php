<?php

use App\Http\Controllers\SocialOAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/privacy', 'pages.privacy')->name('privacy');

Route::view('/login', 'pages.auth.login')->name('login');
Route::view('/register', 'pages.auth.register')->name('register');
Route::view('/forgot-password', 'pages.auth.forgot-password')->name('forgot-password');
Route::view('/reset-password', 'pages.auth.reset-password')->name('reset-password');

Route::view('/products/{id}', 'pages.products.show')
    ->where('id', '[0-9]+')
    ->name('products.show');

Route::view('/cart', 'pages.cart')->name('cart');
Route::view('/checkout', 'pages.checkout')->name('checkout');
Route::view('/orders', 'pages.orders.index')->name('orders.index');
Route::view('/orders/{id}', 'pages.orders.show')
    ->where('id', '[0-9]+')
    ->name('orders.show');
Route::view('/wishlist', 'pages.wishlist')->name('wishlist');
Route::view('/addresses', 'pages.addresses')->name('addresses');
Route::view('/profile', 'pages.profile')->name('profile');

// Seller area
Route::view('/me/store', 'pages.seller.store')->name('me.store');
Route::view('/me/products', 'pages.seller.products.index')->name('me.products.index');
Route::view('/me/products/new', 'pages.seller.products.create')->name('me.products.create');
Route::view('/me/products/{id}/edit', 'pages.seller.products.edit')
    ->where('id', '[0-9]+')
    ->name('me.products.edit');

// Public store page
Route::view('/stores/{id}', 'pages.stores.show')
    ->where('id', '[0-9]+')
    ->name('stores.show');

// Social
Route::view('/social/accounts', 'pages.social.accounts')->name('social.accounts');
Route::view('/social/scheduled-posts', 'pages.social.scheduled-posts')->name('social.scheduled-posts');

// Messages (chat)
Route::view('/messages', 'pages.messages.index')->name('messages.index');
Route::view('/messages/{id}', 'pages.messages.show')
    ->where('id', '[0-9]+')
    ->name('messages.show');

// Visual search
Route::view('/search/visual', 'pages.search.visual')->name('search.visual');

// Social OAuth (browser-side flow)
Route::get('/oauth/{provider}/start', [SocialOAuthController::class, 'start'])
    ->where('provider', 'facebook|tiktok')
    ->name('oauth.start');
Route::get('/oauth/{provider}/callback', [SocialOAuthController::class, 'callback'])
    ->where('provider', 'facebook|tiktok')
    ->name('oauth.callback');

// Admin
Route::view('/admin', 'pages.admin.index')->name('admin.index');
Route::view('/admin/users', 'pages.admin.users')->name('admin.users');
Route::view('/admin/stores', 'pages.admin.stores')->name('admin.stores');
Route::view('/admin/products', 'pages.admin.products')->name('admin.products');
Route::view('/admin/orders', 'pages.admin.orders')->name('admin.orders');
Route::view('/admin/categories', 'pages.admin.categories')->name('admin.categories');
Route::view('/admin/reports', 'pages.admin.reports')->name('admin.reports');
