@extends('layouts.admin')

@section('title', 'Admin · Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div x-data="adminHome()" x-init="init">
    <div>
        <p class="text-sm text-gray-500">Manage marketplace users, stores, products, categories, brands, banners, and reports.</p>

        {{-- Stat cards (cached 60s server-side) --}}
        <div x-show="stats" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6" style="display:none">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Users</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1" x-text="stats?.users?.total ?? '—'"></p>
                <p class="text-xs text-gray-500 mt-1"><span x-text="stats?.users?.sellers ?? 0"></span> sellers</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Stores</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1" x-text="stats?.stores?.total ?? '—'"></p>
                <p class="text-xs text-gray-500 mt-1"><span x-text="stats?.stores?.pending_verification ?? 0"></span> unverified</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Products</p>
                <p class="text-2xl font-semibold text-gray-900 mt-1" x-text="stats?.products?.total ?? '—'"></p>
                <p class="text-xs text-gray-500 mt-1"><span x-text="stats?.products?.published ?? 0"></span> published</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4"
                 :class="(stats?.reports?.open ?? 0) > 0 ? 'border-red-200 bg-red-50' : ''">
                <p class="text-xs uppercase tracking-wide text-gray-500">Open reports</p>
                <p class="text-2xl font-semibold mt-1"
                   :class="(stats?.reports?.open ?? 0) > 0 ? 'text-red-700' : 'text-gray-900'"
                   x-text="stats?.reports?.open ?? '—'"></p>
                <a href="/admin/reports" class="text-xs text-indigo-600 hover:text-indigo-700 mt-1 inline-block">Review →</a>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-8">
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
            <a href="/admin/categories" class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition">
                <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <p class="font-semibold text-gray-900">Categories</p>
                <p class="text-xs text-gray-500 mt-1">Manage marketplace categories &amp; logos</p>
            </a>
            <a href="/admin/reports" class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg transition relative">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21l1.5-7.5a4 4 0 014-3.5h11a4 4 0 014 3.5L25 21M12 3v4m0 4h.01"/></svg>
                </div>
                <p class="font-semibold text-gray-900">Reports</p>
                <p class="text-xs text-gray-500 mt-1">Buyer-flagged products to review</p>
                <span x-show="(stats?.reports?.open ?? 0) > 0"
                      class="absolute top-3 right-3 bg-red-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full"
                      x-text="stats?.reports?.open" style="display:none"></span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function adminHome() {
        return {
            stats: null,
            async init() {
                await this.fetchStats();
            },
            async fetchStats() {
                try {
                    const { data } = await window.api.get('/admin/stats');
                    this.stats = data.stats;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
