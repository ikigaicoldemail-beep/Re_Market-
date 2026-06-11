@extends('layouts.app')

@section('title', 'About')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 prose">
    <h1 class="text-3xl font-semibold text-gray-900 mb-6">About ReMarket</h1>

    <p class="text-sm text-gray-500 mb-8">A second-hand marketplace built for buyers and sellers.</p>

    <section class="space-y-6 text-gray-700 text-sm leading-relaxed">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Who we are</h2>
            <p>ReMarket is an online marketplace for buying and selling quality second-hand goods. We connect people looking to give their pre-loved items a new home with buyers searching for great deals, all in one trusted place.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">What we do</h2>
            <p>Sellers can list products in minutes with photos, descriptions, and prices, and manage their own store. Buyers can browse by category, compare items, build a wishlist, and message sellers directly to ask questions before they buy.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Why second-hand</h2>
            <p>Reusing goods keeps usable items out of landfills and makes quality products more affordable. Every purchase on ReMarket extends the life of something that still has plenty to give.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Get in touch</h2>
            <p>Questions, feedback, or partnership ideas? Email us at <a href="mailto:services@ikigai2.com" class="text-indigo-600 underline">services@ikigai2.com</a>.</p>
        </div>
    </section>
</div>
@endsection
