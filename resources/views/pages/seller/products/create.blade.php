@extends('layouts.app')

@section('title', 'New Product')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="productForm({ mode: 'create' })" x-init="init">
    <a href="/me/products" class="text-sm text-indigo-600 hover:text-indigo-700 mb-4 inline-block">← Back to my products</a>
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">List a new product</h1>

    <div x-show="initLoading" class="text-center py-20 text-gray-500">Loading...</div>

    <div x-show="needsStore" class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center" style="display:none">
        <p class="text-gray-700 mb-3">You need to create a store before listing products.</p>
        <a href="/me/store" class="inline-block bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Create my store</a>
    </div>

    @include('pages.seller.products._form')
</div>

@push('scripts')
    @include('pages.seller.products._form-script')
@endpush
@endsection
