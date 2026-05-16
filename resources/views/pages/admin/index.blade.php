@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="adminHome()" x-init="init">
    <div x-show="!authorized && checked" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center" style="display:none">
        <p class="text-red-700 font-medium">Access denied.</p>
        <p class="text-sm text-red-600 mt-1">This area is restricted to administrators.</p>
        <a href="/" class="inline-block mt-4 bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Back home</a>
    </div>

    <div x-show="authorized" style="display:none">
        <h1 class="text-2xl font-semibold text-gray-900">Admin Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Manage marketplace users, stores, products, and orders.</p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
            <a href="/admin/users" class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <p class="font-semibold text-gray-900">Users</p>
                <p class="text-xs text-gray-500 mt-1">Manage accounts, roles, and status</p>
            </a>
            <a href="/admin/stores" class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18l-2 13H5L3 3zm6 17a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="font-semibold text-gray-900">Stores</p>
                <p class="text-xs text-gray-500 mt-1">Approve and moderate storefronts</p>
            </a>
            <a href="/admin/products" class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <p class="font-semibold text-gray-900">Products</p>
                <p class="text-xs text-gray-500 mt-1">Review and moderate listings</p>
            </a>
            <a href="/admin/orders" class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <p class="font-semibold text-gray-900">Orders</p>
                <p class="text-xs text-gray-500 mt-1">Track and update fulfillment</p>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function adminHome() {
        return {
            authorized: false,
            checked: false,
            async init() {
                const user = window.auth.user();
                if (user?.role === 'admin') {
                    this.authorized = true;
                    this.checked = true;
                    return;
                }
                try {
                    const { data } = await window.api.get('/admin/users', { params: { per_page: 1 } });
                    this.authorized = !!data;
                } catch {
                    this.authorized = false;
                } finally {
                    this.checked = true;
                }
            },
        };
    }
</script>
@endpush
@endsection
