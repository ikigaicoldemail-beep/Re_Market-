@extends('layouts.app')

@section('title', 'Help')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 prose">
    <h1 class="text-3xl font-semibold text-gray-900 mb-6">Help &amp; Support</h1>

    <p class="text-sm text-gray-500 mb-8">Answers to common questions about using ReMarket.</p>

    <section class="space-y-6 text-gray-700 text-sm leading-relaxed">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Creating an account</h2>
            <p>Click <a href="{{ route('register') }}" class="text-indigo-600 underline">Sign up</a> and register with your name, email, and a password. Once verified, you can buy, sell, and message other users.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Buying an item</h2>
            <p>Browse products by category or search, open a listing to see details and photos, and use <a href="{{ route('messages.index') }}" class="text-indigo-600 underline">Messages</a> to ask the seller questions. Save items you like to your <a href="{{ route('wishlist') }}" class="text-indigo-600 underline">Wishlist</a> or add them to <a href="{{ route('compare') }}" class="text-indigo-600 underline">Compare</a>.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Selling an item</h2>
            <p>Set up your store from <a href="{{ route('me.store') }}" class="text-indigo-600 underline">My Store</a>, then add a listing with clear photos, an honest description, the condition, and a fair price. You can manage and edit your listings any time from <a href="{{ route('me.products.index') }}" class="text-indigo-600 underline">My Products</a>.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Managing your profile</h2>
            <p>Update your details and saved addresses from your <a href="{{ route('profile') }}" class="text-indigo-600 underline">Profile</a>. You control your account information at all times.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Still need help?</h2>
            <p>If you can't find what you need, email our team at <a href="mailto:services@ikigai2.com" class="text-indigo-600 underline">services@ikigai2.com</a> and we'll get back to you.</p>
        </div>
    </section>
</div>
@endsection
