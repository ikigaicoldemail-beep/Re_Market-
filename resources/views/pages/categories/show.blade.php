@extends('layouts.app')

@section('title', 'Category')

@section('content')
@include('components.toast')

<div x-data="categoryShow()" x-init="init">
    <div x-show="loading" class="text-center py-20 text-gray-500" data-i18n="common.loading">Loading category...</div>

    <div x-show="!loading && !category" class="text-center py-20" style="display:none">
        <p class="text-gray-600 mb-3" data-i18n="categories.not_found">Category not found.</p>
        <a href="/categories" class="text-indigo-600 hover:text-indigo-700" data-i18n="categories.back">Back to categories</a>
    </div>

    <template x-if="category">
        <div>
            {{-- Header --}}
            <div class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <nav class="text-sm text-indigo-100 mb-4">
                        <a href="/categories" class="hover:underline" data-i18n="home.categories">Categories</a>
                        <template x-if="parent">
                            <span>
                                <span class="mx-2">›</span>
                                <a :href="'/categories/' + parent.slug" class="hover:underline" x-text="parent.name"></a>
                            </span>
                        </template>
                        <span class="mx-2">›</span>
                        <span class="text-white font-medium" x-text="category.name"></span>
                    </nav>

                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 overflow-hidden flex items-center justify-center shrink-0">
                            <template x-if="category.logo_url">
                                <img :src="category.logo_url" :alt="category.name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!category.logo_url">
                                <span class="text-3xl font-bold" x-text="category.name.charAt(0).toUpperCase()"></span>
                            </template>
                        </div>
                        <div>
                            <h1 class="text-3xl font-semibold" x-text="category.name"></h1>
                            <p class="text-indigo-100 text-sm mt-1" x-show="category.description" x-text="category.description" style="display:none"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                {{-- Subcategory tiles (only when on a top-level category that HAS children) --}}
                <div x-show="subCategories.length > 0" class="mb-8" style="display:none">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3" data-i18n="categories.subcategories">Subcategories</h2>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                        <button @click="selectSub('')"
                            :class="!activeSubId ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'hover:bg-gray-50'"
                            class="text-center p-3 rounded-xl border border-gray-200 bg-white transition">
                            <div class="w-12 h-12 mx-auto rounded-full bg-gradient-to-br from-indigo-100 to-pink-100 flex items-center justify-center text-indigo-600 font-semibold" data-i18n="categories.all">All</div>
                            <p class="text-xs font-medium text-gray-700 mt-2" data-i18n="categories.all">All</p>
                        </button>
                        <template x-for="sub in subCategories" :key="sub.id">
                            <button @click="selectSub(sub.id)"
                                :class="activeSubId == sub.id ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'hover:bg-gray-50'"
                                class="text-center p-3 rounded-xl border border-gray-200 bg-white transition">
                                <template x-if="sub.logo_url">
                                    <img :src="sub.logo_url" :alt="sub.name" class="w-12 h-12 mx-auto rounded-full object-cover bg-gray-100">
                                </template>
                                <template x-if="!sub.logo_url">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-gradient-to-br from-indigo-100 to-pink-100 flex items-center justify-center text-indigo-600 font-semibold"
                                         x-text="sub.name.charAt(0)"></div>
                                </template>
                                <p class="text-xs font-medium text-gray-700 mt-2 truncate" x-text="sub.name"></p>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Products grid --}}
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <span data-i18n="categories.products_in">Products in</span> <span x-text="activeName"></span>
                    </h2>
                    <div class="flex items-center gap-3">
                        <select x-model="sort" @change="resetAndFetch()" class="text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="latest" data-i18n="stores.sort_latest">Newest</option>
                            <option value="price_asc" data-i18n="filters.sort_price_asc">Price: low to high</option>
                            <option value="price_desc" data-i18n="filters.sort_price_desc">Price: high to low</option>
                        </select>
                        <span class="text-sm text-gray-500" x-text="meta.total + ' ' + (window.t ? window.t('common.items') : 'items')"></span>
                    </div>
                </div>

                <div x-show="productsLoading && products.length === 0" class="text-center py-16 text-gray-500" data-i18n="home.loading_products" style="display:none">Loading products...</div>

                <div x-show="!productsLoading && products.length === 0" class="text-center py-16 bg-white rounded-xl border border-gray-200" style="display:none">
                    <p class="text-gray-500" data-i18n="categories.no_products">No products in this category yet.</p>
                </div>

                <div x-show="products.length > 0" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4" style="display:none">
                    <template x-for="product in products" :key="product.id">
                        <a :href="'/products/' + product.id"
                            class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition group">
                            <div class="aspect-square bg-gray-100 overflow-hidden relative">
                                <img :src="primaryImage(product)" :alt="product.title" loading="lazy"
                                    onerror="this.src='https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image'"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </div>
                            <div class="p-3">
                                <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-1.5" x-text="product.title"></h3>
                                <p class="text-lg font-bold text-indigo-600 leading-tight" x-text="formatPrice(product.price_amount, product.currency)"></p>
                                <p class="text-xs text-gray-400 mt-1 truncate" x-text="product.location_city || ''"></p>
                            </div>
                        </a>
                    </template>
                </div>

                <div x-show="meta.last_page > 1" class="flex items-center justify-center gap-2 mt-8" style="display:none">
                    <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1" data-i18n="common.previous"
                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">Previous</button>
                    <span class="text-sm text-gray-600 px-3">
                        Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span>
                    </span>
                    <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page" data-i18n="common.next"
                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">Next</button>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
    function categoryShow() {
        return {
            slug: null,
            categories: [],
            category: null,
            parent: null,
            activeSubId: '',
            sort: 'latest',
            products: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            page: 1,
            loading: true,
            productsLoading: false,

            get subCategories() {
                if (!this.category) return [];
                return this.categories.filter(c => c.parent_id === this.category.id && c.status === 'active');
            },
            get activeName() {
                if (this.activeSubId) {
                    const sub = this.categories.find(c => c.id == this.activeSubId);
                    return sub ? sub.name : (this.category?.name || '');
                }
                return this.category?.name || '';
            },
            get effectiveCategoryId() {
                return this.activeSubId || this.category?.id || null;
            },

            async init() {
                const segments = window.location.pathname.split('/').filter(Boolean);
                this.slug = segments[segments.length - 1];

                try {
                    const { data } = await window.api.get('/categories');
                    this.categories = data.categories || [];
                    this.category = this.categories.find(c => c.slug === this.slug) || null;
                    if (this.category?.parent_id) {
                        this.parent = this.categories.find(c => c.id === this.category.parent_id) || null;
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }

                if (this.category) {
                    await this.fetchProducts();
                }
            },

            async selectSub(id) {
                this.activeSubId = id;
                this.page = 1;
                await this.fetchProducts();
            },

            primaryImage(product) {
                if (product.images && product.images.length > 0) {
                    const primary = product.images.find(i => i.is_primary) || product.images[0];
                    return primary.urls?.card_webp || primary.urls?.card || primary.url;
                }
                return 'https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image';
            },

            async fetchProducts() {
                if (!this.effectiveCategoryId) return;
                this.productsLoading = true;
                try {
                    const params = {
                        category_id: this.effectiveCategoryId,
                        page: this.page,
                        sort: this.sort,
                        per_page: 20,
                    };
                    const { data } = await window.api.get('/products', { params });
                    this.products = data.products || [];
                    this.meta = data.meta || this.meta;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.productsLoading = false;
                }
            },

            resetAndFetch() {
                this.page = 1;
                this.fetchProducts();
            },

            goToPage(page) {
                this.page = page;
                this.fetchProducts();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
        };
    }
</script>
@endpush
@endsection
