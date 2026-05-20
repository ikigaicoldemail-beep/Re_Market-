@extends('layouts.app')

@section('title', 'Compare Products')

@section('content')
@include('components.toast')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="comparePage()" x-init="init">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900" data-i18n="compare.title">Compare products</h1>
            <p class="text-sm text-gray-500 mt-1" data-i18n="compare.subtitle">Side-by-side comparison of up to 4 products.</p>
        </div>
        <button x-show="products.length > 0" @click="clearAll()" class="text-sm text-red-600 hover:text-red-700" data-i18n="compare.clear_all" style="display:none">Clear all</button>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500" data-i18n="common.loading">Loading...</div>

    <div x-show="!loading && products.length === 0" class="text-center py-20 bg-white rounded-xl border border-gray-200" style="display:none">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-gray-600 mb-2" data-i18n="compare.empty">No products in your compare list yet.</p>
        <p class="text-sm text-gray-500 mb-4" data-i18n="compare.hint">Browse products and tap "Add to compare" to add up to 4 items.</p>
        <a href="/" class="inline-block bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700" data-i18n="compare.browse_products">Browse products</a>
    </div>

    <div x-show="!loading && products.length > 0" class="bg-white border border-gray-200 rounded-xl overflow-x-auto" style="display:none">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40 bg-gray-50" data-i18n="compare.col_product">Product</th>
                    <template x-for="p in products" :key="'h-' + p.id">
                        <th class="px-4 py-4 text-left align-top min-w-[220px]">
                            <div class="relative">
                                <button @click="remove(p.id)" title="Remove" class="absolute -top-1 -right-1 w-6 h-6 bg-white border border-gray-200 rounded-full text-gray-400 hover:text-red-600 hover:border-red-300">✕</button>
                                <a :href="'/products/' + p.id" class="block">
                                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-3">
                                        <img :src="primaryImage(p)" :alt="p.title" loading="lazy"
                                            onerror="this.src='https://placehold.co/300x300/e5e7eb/9ca3af?text=No+Image'"
                                            class="w-full h-full object-cover">
                                    </div>
                                    <h3 class="font-medium text-gray-900 line-clamp-2 hover:text-indigo-600" x-text="p.title"></h3>
                                </a>
                            </div>
                        </th>
                    </template>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 bg-gray-50" data-i18n="compare.col_price">Price</th>
                    <template x-for="p in products" :key="'price-' + p.id">
                        <td class="px-4 py-3 align-top">
                            <div class="flex items-baseline gap-2">
                                <span class="text-lg font-bold text-indigo-600" x-text="formatPrice(p.price_amount, p.currency)"></span>
                                <span x-show="p.original_price_amount && p.original_price_amount > p.price_amount" class="text-xs text-gray-400 line-through" x-text="formatPrice(p.original_price_amount, p.currency)" style="display:none"></span>
                            </div>
                        </td>
                    </template>
                </tr>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 bg-gray-50" data-i18n="compare.col_brand">Brand</th>
                    <template x-for="p in products" :key="'brand-' + p.id">
                        <td class="px-4 py-3 align-top text-gray-700" x-text="p.brand?.name || '—'"></td>
                    </template>
                </tr>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 bg-gray-50" data-i18n="compare.col_category">Category</th>
                    <template x-for="p in products" :key="'cat-' + p.id">
                        <td class="px-4 py-3 align-top text-gray-700" x-text="p.category?.name || '—'"></td>
                    </template>
                </tr>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 bg-gray-50" data-i18n="compare.col_condition">Condition</th>
                    <template x-for="p in products" :key="'cond-' + p.id">
                        <td class="px-4 py-3 align-top text-gray-700" x-text="p.condition?.name || '—'"></td>
                    </template>
                </tr>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 bg-gray-50" data-i18n="compare.col_location">Location</th>
                    <template x-for="p in products" :key="'loc-' + p.id">
                        <td class="px-4 py-3 align-top text-gray-700" x-text="[p.location_city, p.location_state, p.location_country_code].filter(Boolean).join(', ') || '—'"></td>
                    </template>
                </tr>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 bg-gray-50" data-i18n="compare.col_stock">Stock</th>
                    <template x-for="p in products" :key="'stock-' + p.id">
                        <td class="px-4 py-3 align-top text-gray-700">
                            <span x-show="p.stock_quantity > 0" :class="p.stock_quantity > 0 ? 'text-green-700' : 'text-red-600'" x-text="p.stock_quantity + ' in stock'"></span>
                            <span x-show="p.stock_quantity <= 0" class="text-red-600" style="display:none">Out of stock</span>
                        </td>
                    </template>
                </tr>
                <template x-for="key in allSpecKeys" :key="'spec-' + key">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 bg-gray-50" x-text="key"></th>
                        <template x-for="p in products" :key="'sv-' + p.id + '-' + key">
                            <td class="px-4 py-3 align-top text-gray-700" x-text="(p.specs && p.specs[key]) || '—'"></td>
                        </template>
                    </tr>
                </template>
                <tr>
                    <th class="px-4 py-3 bg-gray-50"></th>
                    <template x-for="p in products" :key="'act-' + p.id">
                        <td class="px-4 py-3 align-top">
                            <a :href="'/products/' + p.id" class="inline-block bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-indigo-700" data-i18n="compare.view">View</a>
                        </td>
                    </template>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    function comparePage() {
        return {
            products: [],
            loading: true,
            async init() {
                const ids = Alpine.store('compare').ids;
                if (ids.length === 0) {
                    this.loading = false;
                    return;
                }
                try {
                    const results = await Promise.all(ids.map(id => window.api.get('/products/' + id).then(r => r.data.product).catch(() => null)));
                    this.products = results.filter(Boolean);
                    // Prune stale ids whose products no longer exist
                    const validIds = this.products.map(p => p.id);
                    if (validIds.length !== ids.length) {
                        Alpine.store('compare').ids = validIds;
                        Alpine.store('compare').persist();
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            get allSpecKeys() {
                const set = new Set();
                this.products.forEach(p => {
                    if (p.specs && typeof p.specs === 'object') {
                        Object.keys(p.specs).forEach(k => set.add(k));
                    }
                });
                return Array.from(set);
            },
            primaryImage(product) {
                if (product.images && product.images.length > 0) {
                    const primary = product.images.find(i => i.is_primary) || product.images[0];
                    return primary.urls?.card_webp || primary.urls?.card || primary.url;
                }
                return 'https://placehold.co/300x300/e5e7eb/9ca3af?text=No+Image';
            },
            remove(id) {
                Alpine.store('compare').remove(id);
                this.products = this.products.filter(p => p.id !== Number(id));
            },
            clearAll() {
                Alpine.store('compare').clear();
                this.products = [];
            },
        };
    }
</script>
@endpush
@endsection
