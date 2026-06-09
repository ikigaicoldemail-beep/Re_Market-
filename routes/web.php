<?php

use App\Http\Controllers\SocialOAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/privacy', 'pages.privacy')->name('privacy');

Route::view('/login', 'pages.auth.login')->name('login');
Route::view('/register', 'pages.auth.register')->name('register');
Route::view('/forgot-password', 'pages.auth.forgot-password')->name('forgot-password');
Route::view('/reset-password', 'pages.auth.reset-password')->name('reset-password');

Route::get('/products/{id}', function ($id) {
    $product = \App\Models\Product::with(['images'])->find($id);
    return view('pages.products.show', ['id' => (int) $id, 'ogProduct' => $product]);
})->where('id', '[0-9]+')->name('products.show');

Route::view('/wishlist', 'pages.wishlist')->name('wishlist');
Route::view('/compare', 'pages.compare')->name('compare');
Route::view('/profile', 'pages.profile')->name('profile');

// Seller area
Route::view('/me/store', 'pages.seller.store')->name('me.store');
Route::view('/me/products', 'pages.seller.products.index')->name('me.products.index');
Route::view('/me/products/new', 'pages.seller.products.create')->name('me.products.create');
Route::view('/me/products/{id}/edit', 'pages.seller.products.edit')
    ->where('id', '[0-9]+')
    ->name('me.products.edit');
Route::view('/me/scheduled-listings', 'pages.seller.products.scheduled')->name('me.scheduled-listings');

// Public stores
Route::view('/stores', 'pages.stores.index')->name('stores.index');
Route::view('/stores/{id}', 'pages.stores.show')
    ->name('stores.show');

// Public categories browse
Route::view('/categories', 'pages.categories.index')->name('categories.index');
Route::view('/categories/{slug}', 'pages.categories.show')->name('categories.show');

// Social
Route::view('/social/accounts', 'pages.social.accounts')->name('social.accounts');
Route::view('/social/scheduled-posts', 'pages.social.scheduled-posts')->name('social.scheduled-posts');

// Messages (chat)
Route::view('/messages', 'pages.messages.index')->name('messages.index');
Route::view('/messages/{id}', 'pages.messages.show')
    ->where('id', '[0-9]+')
    ->name('messages.show');

// Social OAuth (browser-side flow)
Route::get('/oauth/{provider}/start', [SocialOAuthController::class, 'start'])
    ->where('provider', 'facebook')
    ->name('oauth.start');
Route::get('/oauth/{provider}/callback', [SocialOAuthController::class, 'callback'])
    ->where('provider', 'facebook')
    ->name('oauth.callback');

// Admin — client-side guard (admin-guard component) redirects to /login if
// not authenticated; API routes enforce role:admin server-side.
Route::view('/admin', 'pages.admin.index')->name('admin.index');
Route::view('/admin/users', 'pages.admin.users')->name('admin.users');
Route::view('/admin/stores', 'pages.admin.stores')->name('admin.stores');
Route::view('/admin/products', 'pages.admin.products')->name('admin.products');
Route::view('/admin/categories', 'pages.admin.categories')->name('admin.categories');
Route::view('/admin/brands', 'pages.admin.brands')->name('admin.brands');
Route::view('/admin/banners', 'pages.admin.banners')->name('admin.banners');
Route::view('/admin/reports', 'pages.admin.reports')->name('admin.reports');
