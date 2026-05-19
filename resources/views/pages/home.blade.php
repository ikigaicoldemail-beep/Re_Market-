@extends('layouts.app')

@section('title', 'Browse Products')

@section('content')
@include('components.toast')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="productList()" x-init="init">
    {{-- Top-level category cards (khmer24-style) --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Categories</h2>
            <button x-show="filters.category_id" @click="clearCategory()"
                class="text-xs text-indigo-600 hover:text-indigo-700 font-medium" style="display:none">
                Show all categories
            </button>
        </div>
        <div class="flex gap-3 overflow-x-auto pb-2 -mx-1 px-1">
            <template x-for="cat in parentCategories" :key="cat.id">
                <button @click="selectParent(cat.id)"
                    :class="filters.category_id == cat.id ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'hover:bg-gray-50'"
                    class="shrink-0 w-24 text-center p-2 rounded-xl border border-gray-200 bg-white transition">
                    <template x-if="cat.logo_url">
                        <img :src="cat.logo_url" :alt="cat.name" class="w-14 h-14 mx-auto rounded-full object-cover bg-gray-100">
                    </template>
                    <template x-if="!cat.logo_url">
                        <div class="w-14 h-14 mx-auto rounded-full bg-gradient-to-br from-indigo-100 to-pink-100 flex items-center justify-center text-indigo-600 font-semibold"
                             x-text="cat.name.charAt(0)"></div>
                    </template>
                    <p class="text-xs font-medium text-gray-700 mt-1 truncate" x-text="cat.name"></p>
                </button>
            </template>
        </div>

        {{-- Child chip row, visible only when a parent is selected --}}
        <div x-show="childChips.length > 0" class="flex flex-wrap gap-2 mt-3" style="display:none">
            <button @click="selectChild('')"
                :class="!filters.sub_category_id ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="text-xs px-3 py-1.5 rounded-full border border-gray-200 font-medium transition">
                All in <span x-text="selectedParentName()"></span>
            </button>
            <template x-for="child in childChips" :key="child.id">
                <button @click="selectChild(child.id)"
                    :class="filters.sub_category_id == child.id ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    class="text-xs px-3 py-1.5 rounded-full border border-gray-200 font-medium transition"
                    x-text="child.name"></button>
            </template>
        </div>
    </div>

    {{-- Province / city filter --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Province</h2>
            <button x-show="filters.location_city" @click="selectCity('')"
                class="text-xs text-indigo-600 hover:text-indigo-700 font-medium" style="display:none">
                All provinces
            </button>
        </div>
        <div class="flex flex-wrap gap-2">
            <button @click="selectCity('')"
                :class="!filters.location_city ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="text-xs px-3 py-1.5 rounded-full border border-gray-200 font-medium transition">
                All
            </button>
            <template x-for="city in provinces" :key="city">
                <button @click="selectCity(city)"
                    :class="filters.location_city === city ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    class="text-xs px-3 py-1.5 rounded-full border border-gray-200 font-medium transition"
                    x-text="city"></button>
            </template>
        </div>
    </div>

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
                        class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition group flex flex-col">
                        <div class="aspect-square bg-gray-100 overflow-hidden relative">
                            <img :src="primaryImage(product)" :alt="product.title" loading="lazy"
                                onerror="this.src='https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image'"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <span x-show="product.condition"
                                class="absolute top-2 left-2 text-[10px] font-semibold px-2 py-0.5 rounded-full backdrop-blur-sm"
                                :class="conditionChipClasses(product.condition?.color)"
                                x-text="product.condition?.name" style="display:none"></span>
                        </div>
                        <div class="p-3 flex-1 flex flex-col">
                            <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-1.5" x-text="product.title"></h3>
                            <p class="text-lg font-bold text-indigo-600 leading-tight" x-text="formatPrice(product.price_amount, product.currency)"></p>
                            <div x-show="product.reviews_count > 0" class="flex items-center gap-1 mt-1 text-xs" style="display:none">
                                <span class="text-yellow-400">★</span>
                                <span class="font-medium text-gray-700" x-text="(product.reviews_avg_rating ?? 0).toFixed(1)"></span>
                                <span class="text-gray-400" x-text="'(' + product.reviews_count + ')'"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500 mt-1.5">
                                <span class="truncate" x-text="product.location_city || product.location_country_code || ''"></span>
                                <span class="shrink-0 ml-2" x-text="formatRelativeTime(product.published_at || product.created_at)"></span>
                            </div>
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
                sub_category_id: '',
                product_condition_id: '',
                location_city: '',
                min_price: '',
                max_price: '',
                sort: 'latest',
                page: 1,
            },
            provinces: [
                'Phnom Penh', 'Siem Reap', 'Battambang', 'Sihanoukville',
                'Kandal', 'Kampong Cham', 'Kampot', 'Kep',
            ],
            loading: false,
            async init() {
                const params = new URLSearchParams(window.location.search);
                if (params.get('search')) this.filters.search = params.get('search');
                if (params.get('category')) this.filters.category_id = params.get('category');
                if (params.get('sub')) this.filters.sub_category_id = params.get('sub');
                if (params.get('city')) this.filters.location_city = params.get('city');
                await Promise.all([this.fetchProducts(), this.fetchCategories(), this.fetchConditions()]);
            },
            get parentCategories() {
                return this.categories.filter(c => !c.parent_id);
            },
            get childChips() {
                const pid = parseInt(this.filters.category_id, 10);
                if (!pid) return [];
                return this.categories.filter(c => c.parent_id === pid);
            },
            selectedParentName() {
                const id = parseInt(this.filters.category_id, 10);
                const p = this.categories.find(c => c.id === id);
                return p ? p.name : '';
            },
            selectParent(id) {
                this.filters.category_id = (this.filters.category_id == id) ? '' : id;
                this.filters.sub_category_id = '';
                this.syncUrl();
                this.resetAndFetch();
            },
            selectChild(id) {
                this.filters.sub_category_id = id;
                this.syncUrl();
                this.resetAndFetch();
            },
            clearCategory() {
                this.filters.category_id = '';
                this.filters.sub_category_id = '';
                this.syncUrl();
                this.resetAndFetch();
            },
            selectCity(name) {
                this.filters.location_city = name;
                this.syncUrl();
                this.resetAndFetch();
            },
            syncUrl() {
                const p = new URLSearchParams(window.location.search);
                if (this.filters.category_id) p.set('category', this.filters.category_id);
                else p.delete('category');
                if (this.filters.sub_category_id) p.set('sub', this.filters.sub_category_id);
                else p.delete('sub');
                if (this.filters.location_city) p.set('city', this.filters.location_city);
                else p.delete('city');
                const qs = p.toString();
                window.history.replaceState(null, '', window.location.pathname + (qs ? '?' + qs : ''));
            },
            primaryImage(product) {
                if (product.images && product.images.length > 0) {
                    const primary = product.images.find(i => i.is_primary) || product.images[0];
                    return primary.urls?.card_webp || primary.urls?.card || primary.url;
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
                        if (k === 'sub_category_id') return; // handled below
                        if (v !== '' && v !== null && v !== undefined) params[k] = v;
                    });
                    // If a sub-category chip is selected, narrow to that exact child;
                    // otherwise leave the parent in place so children are included server-side.
                    if (this.filters.sub_category_id) {
                        params.category_id = this.filters.sub_category_id;
                    }
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
                    search: '', category_id: '', sub_category_id: '', product_condition_id: '',
                    location_city: '', min_price: '', max_price: '', sort: 'latest', page: 1,
                };
                this.syncUrl();
                this.fetchProducts();
            },
        };
    }
</script>
@endpush
@endsection
