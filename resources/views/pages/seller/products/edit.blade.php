@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="productForm({ mode: 'edit' })" x-init="init">
    <a href="/me/products" class="text-sm text-indigo-600 hover:text-indigo-700 mb-4 inline-block">← Back to my products</a>
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Edit product</h1>

    <div x-show="initLoading" class="text-center py-20 text-gray-500">Loading...</div>

    @include('pages.seller.products._form')
</div>

@push('scripts')
    @include('pages.seller.products._form-script')
@endpush
@endsection
