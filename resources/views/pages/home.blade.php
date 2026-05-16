@extends('layouts.app')

@section('title', 'Browse Products')

@section('content')
@include('components.toast')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="productList()" x-init="init">
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Filters Sidebar --}}
        <aside class="lg:w-64 shrink-0">
            <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-20">
                <h2 class="font-semibold text-gray-900 mb-4">Filters</h2>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" x-model.debounce.400ms="filters.search" @input="resetAndFetch()"
                        placeholder="Title, description..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select x-model="filters.category_id" @change="resetAndFetch()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All categories</option>
                        <template x-for="c in categories" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
                    <select x-model="filters.product_condition_id" @change="resetAndFetch()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Any condition</option>
                        <template x-for="c in conditions" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price range</label>
                    <div class="flex gap-2">
                        <input type="number" x-model.number="filters.min_price" @change="resetAndFetch()" placeholder="Min"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <input type="number" x-model.number="filters.max_price" @change="resetAndFetch()" placeholder="Max"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">In cents (e.g. 1000 = $10)</p>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort by</label>
                    <select x-model="filters.sort" @change="resetAndFetch()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="latest">Newest first</option>
                        <option value="oldest">Oldest first</option>
                        <option value="price_asc">Price: low to high</option>
                        <option value="price_desc">Price: high to low</option>
                    </select>
                </div>

                <button @click="reset()" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                    Clear filters
                </button>
            </div>
        </aside>

        {{-- Product Grid --}}
        <div class="flex-1">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Browse</h1>
                <span class="text-sm text-gray-500" x-text="meta.total + ' items'"></span>
            </div>

            <div x-show="loading && products.length === 0" class="text-center py-20 text-gray-500" style="display:none">
                Loading products...
            </div>

            <div x-show="!loading && products.length === 0" class="text-center py-20 bg-white rounded-xl border border-gray-200" style="display:none">
                <p class="text-gray-500">No products found. Try adjusting your filters.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="product in products" :key="product.id">
                    <a :href="'/products/' + product.id"
                        class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition group">
                        <div class="aspect-square bg-gray-100 overflow-hidden">
                            <img :src="primaryImage(product)" :alt="product.title"
                                onerror="this.src='https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image'"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        </div>
                        <div class="p-3">
                            <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-1" x-text="product.title"></h3>
                            <p class="text-indigo-600 font-semibold" x-text="formatPrice(product.price_amount, product.currency)"></p>
                            <p class="text-xs text-gray-500 mt-1" x-text="product.location_city || product.location_country_code || ''"></p>
                        </div>
                    </a>
                </template>
            </div>

            {{-- Pagination --}}
            <div x-show="meta.last_page > 1" class="flex items-center justify-center gap-2 mt-8" style="display:none">
                <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <span class="text-sm text-gray-600 px-3">
                    Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span>
                </span>
                <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function productList() {
        return {
            products: [],
            categories: [],
            conditions: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            filters: {
                search: '',
                category_id: '',
                product_condition_id: '',
                min_price: '',
                max_price: '',
                sort: 'latest',
                page: 1,
            },
            loading: false,
            async init() {
                const params = new URLSearchParams(window.location.search);
                if (params.get('search')) this.filters.search = params.get('search');
                await Promise.all([this.fetchProducts(), this.fetchCategories(), this.fetchConditions()]);
            },
            primaryImage(product) {
                if (product.images && product.images.length > 0) {
                    const primary = product.images.find(i => i.is_primary) || product.images[0];
                    return primary.url;
                }
                return 'https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image';
            },
            async fetchCategories() {
                try {
                    const { data } = await window.api.get('/categories');
                    this.categories = data.categories || [];
                } catch {}
            },
            async fetchConditions() {
                try {
                    const { data } = await window.api.get('/product-conditions');
                    this.conditions = data.product_conditions || [];
                } catch {}
            },
            async fetchProducts() {
                this.loading = true;
                try {
                    const params = {};
                    Object.entries(this.filters).forEach(([k, v]) => {
                        if (v !== '' && v !== null && v !== undefined) params[k] = v;
                    });
                    const { data } = await window.api.get('/products', { params });
                    this.products = data.products || [];
                    this.meta = data.meta || this.meta;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            resetAndFetch() {
                this.filters.page = 1;
                this.fetchProducts();
            },
            goToPage(page) {
                this.filters.page = page;
                this.fetchProducts();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            reset() {
                this.filters = {
                    search: '', category_id: '', product_condition_id: '',
                    min_price: '', max_price: '', sort: 'latest', page: 1,
                };
                this.fetchProducts();
            },
        };
    }
</script>
@endpush
@endsection
