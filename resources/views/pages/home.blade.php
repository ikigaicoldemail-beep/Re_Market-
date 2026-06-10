@extends('layouts.app')

@section('title', 'Browse Products')

@section('content')
@include('components.toast')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="productList()"
    x-init="init"
    @visual-search:file.window="searchByPhotoFile($event.detail.file)"
    @paste.window="handleVisualPaste($event)"
    @dragenter.window="handleVisualDragEnter($event)"
    @dragover.window="handleVisualDragOver($event)"
    @dragleave.window="handleVisualDragLeave($event)"
    @drop.window="handleVisualDrop($event)">
    <div x-show="visualDragging" x-transition.opacity
        class="fixed inset-0 z-50 bg-gray-950/60 backdrop-blur-sm flex items-center justify-center px-4"
        style="display:none">
        <div class="w-full max-w-sm rounded-lg border border-white/20 bg-white text-gray-900 shadow-2xl p-6 text-center">
            <div class="w-12 h-12 mx-auto rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center mb-3">
                <x-heroicon-o-camera class="w-6 h-6"/>
            </div>
            <p class="text-lg font-semibold">Drop image</p>
        </div>
    </div>

    {{-- Hero slider --}}
    <div x-show="banners.length > 0" class="mb-8 relative rounded-2xl overflow-hidden shadow" style="display:none">
        <div class="relative aspect-[5/2] sm:aspect-[12/4] bg-gradient-to-br from-indigo-100 to-pink-100">
            <template x-for="(banner, idx) in banners" :key="banner.id">
                <a :href="banner.link_url || '#'"
                    x-show="idx === bannerIndex"
                    x-transition.opacity.duration.500ms
                    class="absolute inset-0">
                    <img :src="banner.image_url" :alt="banner.title || 'Promotion'"
                        class="w-full h-full object-cover" loading="lazy">
                    <div x-show="banner.title || banner.subtitle" class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent flex flex-col justify-end p-6 text-white" style="display:none">
                        <h3 class="text-2xl sm:text-3xl font-bold" x-show="banner.title" x-text="banner.title" style="display:none"></h3>
                        <p class="text-sm sm:text-base mt-1" x-show="banner.subtitle" x-text="banner.subtitle" style="display:none"></p>
                    </div>
                </a>
            </template>
        </div>
        <div x-show="banners.length > 1" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5" style="display:none">
            <template x-for="(_, idx) in banners" :key="idx">
                <button @click.prevent="bannerIndex = idx"
                    :class="idx === bannerIndex ? 'bg-white w-6' : 'bg-white/60 w-2'"
                    class="h-2 rounded-full transition-all"></button>
            </template>
        </div>
    </div>

    {{-- Brand bar --}}
    <div x-show="brands.length > 0" class="mb-6" style="display:none">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide" data-i18n="home.shop_by_brand">Shop by brand</h2>
            <button x-show="filters.brand_id" @click="selectBrand('')"
                class="text-xs text-indigo-600 hover:text-indigo-700 font-medium" data-i18n="home.all_brands" style="display:none">
                All brands
            </button>
        </div>
        <div class="flex gap-3 overflow-x-auto pb-2 -mx-1 px-1">
            <template x-for="b in brands" :key="b.id">
                <button @click="selectBrand(b.id)"
                    :class="filters.brand_id == b.id ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'hover:bg-gray-50'"
                    class="shrink-0 w-24 text-center p-2 rounded-xl border border-gray-200 bg-white transition">
                    <template x-if="b.logo_url">
                        <img :src="b.logo_url" :alt="b.name" class="w-12 h-12 mx-auto rounded-lg object-contain bg-white">
                    </template>
                    <template x-if="!b.logo_url">
                        <div class="w-12 h-12 mx-auto rounded-lg bg-gradient-to-br from-indigo-100 to-pink-100 flex items-center justify-center text-indigo-600 font-bold"
                             x-text="b.name.charAt(0)"></div>
                    </template>
                    <p class="text-xs font-medium text-gray-700 mt-1 truncate" x-text="b.name"></p>
                </button>
            </template>
        </div>
    </div>

    {{-- Trending carousel --}}
    <div x-show="trending.length > 0" class="mb-8" style="display:none">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <x-heroicon-s-fire class="w-5 h-5 text-amber-500"/>
                <span data-i18n="home.trending">Trending</span>
            </h2>
            <a href="?sort=featured" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium" data-i18n="home.view_all">View all</a>
        </div>
        <div class="flex gap-3 overflow-x-auto pb-2 -mx-1 px-1 snap-x">
            <template x-for="product in trending" :key="'t-' + product.id">
                <a :href="'/products/' + product.id"
                    class="shrink-0 w-44 snap-start bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition group">
                    <div class="aspect-square bg-gray-100 overflow-hidden relative">
                        <img :src="primaryImage(product)" :alt="product.title" loading="lazy"
                            onerror="this.src='https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image'"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <span class="absolute top-2 left-2 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 inline-flex items-center gap-0.5">
                            <x-heroicon-s-fire class="w-3 h-3"/> Trending
                        </span>
                        <span x-show="product.original_price_amount && product.original_price_amount > product.price_amount"
                            class="absolute top-2 right-2 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700" style="display:none"
                            x-text="'-' + Math.round((1 - product.price_amount/product.original_price_amount) * 100) + '%'"></span>
                    </div>
                    <div class="p-3">
                        <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-1.5" x-text="product.title"></h3>
                        <div class="flex items-baseline gap-1.5">
                            <p class="text-base font-bold text-indigo-600" x-text="formatPrice(product.price_amount, product.currency)"></p>
                            <p x-show="product.original_price_amount && product.original_price_amount > product.price_amount"
                                class="text-xs text-gray-400 line-through" style="display:none"
                                x-text="formatPrice(product.original_price_amount, product.currency)"></p>
                        </div>
                    </div>
                </a>
            </template>
        </div>
    </div>

    {{-- Featured Shops --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <x-heroicon-o-building-storefront class="w-5 h-5 text-indigo-500"/>
                <span>Shops</span>
            </h2>
            <a href="{{ route('stores.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">View all</a>
        </div>
        {{-- Skeleton shown while loading --}}
        <div x-show="shops.length === 0" class="flex gap-3 overflow-x-auto pb-2 -mx-1 px-1" style="display:none">
            <template x-for="i in [1,2,3,4,5,6]" :key="i">
                <div class="shrink-0 w-36 rounded-xl border border-gray-100 bg-gray-100 p-3 flex flex-col items-center animate-pulse">
                    <div class="w-14 h-14 rounded-full bg-gray-200 mb-2"></div>
                    <div class="h-2.5 bg-gray-200 rounded w-20 mb-1"></div>
                    <div class="h-2 bg-gray-200 rounded w-14"></div>
                </div>
            </template>
        </div>
        <div x-show="shops.length > 0" class="flex gap-3 overflow-x-auto pb-2 -mx-1 px-1 snap-x" style="display:none">
            <template x-for="shop in shops" :key="'s-' + shop.id">
                <a :href="'/stores/' + shop.slug"
                    class="shrink-0 w-36 snap-start bg-white rounded-xl border border-gray-200 p-3 flex flex-col items-center text-center hover:shadow-md hover:border-indigo-200 transition group">
                    <div class="w-14 h-14 rounded-full overflow-hidden bg-gradient-to-br from-indigo-100 to-pink-100 flex items-center justify-center mb-2 shrink-0">
                        <template x-if="shop.logo_url">
                            <img :src="shop.logo_url" :alt="shop.name" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!shop.logo_url">
                            <span class="text-xl font-bold text-indigo-500" x-text="shop.name.charAt(0).toUpperCase()"></span>
                        </template>
                    </div>
                    <p class="text-xs font-semibold text-gray-900 line-clamp-2 leading-tight mb-0.5 group-hover:text-indigo-700 transition" x-text="shop.name"></p>
                    <p class="text-[11px] text-gray-400 truncate w-full" x-text="shop.city || ''"></p>
                    <p class="text-[11px] text-indigo-500 mt-0.5" x-text="(shop.products_count || 0) + ' listings'"></p>
                </a>
            </template>
        </div>
    </div>

    {{-- Top-level category cards (khmer24-style) --}}
    <div class="mb-6" id="categories">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide" data-i18n="home.categories">Categories</h2>
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
                <h2 class="font-semibold text-gray-900 mb-4" data-i18n="filters.title">Filters</h2>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="filters.search">Search</label>
                    <div class="flex gap-2">
                        <input type="text" x-model.debounce.400ms="filters.search" @input="resetAndFetch()"
                            placeholder="Title, description..." data-i18n="filters.search_placeholder" data-i18n-placeholder
                            class="min-w-0 flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button" @click="$refs.visualSearchInput.click()" :disabled="visualSearching"
                            title="Search by photo"
                            class="w-10 h-10 rounded-lg border border-gray-300 bg-white text-gray-600 hover:text-indigo-700 hover:border-indigo-300 disabled:opacity-50 flex items-center justify-center">
                            <x-heroicon-o-camera class="w-5 h-5"/>
                        </button>
                        <input x-ref="visualSearchInput" type="file" accept="image/png,image/jpeg,image/webp" capture="environment"
                            @change="searchByPhoto($event)" class="hidden">
                    </div>
                    <div x-show="visualSearchActive" class="mt-2 flex items-center gap-2 text-xs text-indigo-700" style="display:none">
                        <span class="inline-flex items-center gap-1">
                            <x-heroicon-o-camera class="w-3.5 h-3.5"/>
                            Photo results
                        </span>
                        <button type="button" @click="clearVisualSearch()" class="font-medium hover:text-indigo-900">Clear</button>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="filters.condition">Condition</label>
                    <select x-model="filters.product_condition_id" @change="resetAndFetch()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" data-i18n="filters.any_condition">Any condition</option>
                        <template x-for="c in conditions" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="filters.price_range">Price range</label>
                    <div class="flex gap-2">
                        <input type="number" x-model.number="filters.min_price" @change="resetAndFetch()" placeholder="Min" data-i18n="filters.min" data-i18n-placeholder
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <input type="number" x-model.number="filters.max_price" @change="resetAndFetch()" placeholder="Max" data-i18n="filters.max" data-i18n-placeholder
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <p class="text-xs text-gray-400 mt-1" data-i18n="filters.price_hint">In cents (e.g. 1000 = $10)</p>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2" data-i18n="filters.sort_by">Sort by</label>
                    <select x-model="filters.sort" @change="resetAndFetch()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="latest" data-i18n="filters.sort_latest">Newest first</option>
                        <option value="oldest" data-i18n="filters.sort_oldest">Oldest first</option>
                        <option value="price_asc" data-i18n="filters.sort_price_asc">Price: low to high</option>
                        <option value="price_desc" data-i18n="filters.sort_price_desc">Price: high to low</option>
                    </select>
                </div>

                <button @click="reset()" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium" data-i18n="filters.clear">
                    Clear filters
                </button>
            </div>
        </aside>

            {{-- Product Grid --}}
            <div class="flex-1">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900" data-i18n="home.browse">Browse</h1>
                        <div x-show="visualSearchActive" class="mt-2 flex items-center gap-2 text-sm text-indigo-700" style="display:none">
                            <span class="inline-flex items-center gap-1.5">
                                <x-heroicon-o-camera class="w-4 h-4"/>
                                Image search results
                            </span>
                            <button type="button" @click="clearVisualSearch()" class="text-xs font-semibold hover:text-indigo-900">Clear</button>
                        </div>
                    </div>
                    <span class="text-sm text-gray-500" x-text="meta.total + ' ' + (window.t ? window.t('common.items') : 'items')"></span>
                </div>

            <div x-show="loading && products.length === 0" class="text-center py-20 text-gray-500" style="display:none">
                <div class="w-10 h-10 mx-auto mb-3 rounded-full border-2 border-indigo-200 border-t-indigo-600 animate-spin"></div>
                <p x-text="visualSearching ? 'Searching by image...' : 'Loading products...'"></p>
            </div>

            <div x-show="!loading && products.length === 0" class="text-center py-20 bg-white rounded-xl border border-gray-200" style="display:none">
                <div x-show="visualSearchActive" class="w-12 h-12 mx-auto rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3" style="display:none">
                    <x-heroicon-o-camera class="w-6 h-6"/>
                </div>
                <p class="text-gray-500" x-text="visualSearchActive ? 'No visual matches found for this image.' : 'No products found. Try adjusting your filters.'"></p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="product in products" :key="product.id">
                    <a :href="'/products/' + product.id"
                        class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition group flex flex-col relative">
                        <button @click.prevent.stop="$store.compare.toggle(product.id)"
                            :title="$store.compare.has(product.id) ? 'In compare' : 'Add to compare'"
                            :class="$store.compare.has(product.id) ? 'bg-indigo-600 text-white' : 'bg-white/90 text-gray-600 hover:text-indigo-700'"
                            class="absolute top-2 right-2 w-7 h-7 rounded-full border border-gray-200 shadow-sm z-10 flex items-center justify-center">
                            <x-heroicon-o-arrows-right-left class="w-3.5 h-3.5"/>
                        </button>
                        <div class="aspect-square bg-gray-100 overflow-hidden relative">
                            <img :src="primaryImage(product)" :alt="product.title" loading="lazy"
                                onerror="this.src='https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image'"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <span x-show="product.condition"
                                class="absolute top-2 left-2 text-[10px] font-semibold px-2 py-0.5 rounded-full backdrop-blur-sm"
                                :class="conditionChipClasses(product.condition?.color)"
                                x-text="product.condition?.name" style="display:none"></span>
                            <span x-show="product.original_price_amount && product.original_price_amount > product.price_amount"
                                class="absolute bottom-2 right-2 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700" style="display:none"
                                x-text="'-' + Math.round((1 - product.price_amount/product.original_price_amount) * 100) + '%'"></span>
                            <span x-show="product.is_featured && !(product.original_price_amount && product.original_price_amount > product.price_amount)"
                                class="absolute bottom-2 right-2 px-1.5 py-1 rounded-full bg-amber-100 text-amber-700" style="display:none">
                                <x-heroicon-s-fire class="w-3 h-3"/>
                            </span>
                            <span x-show="product.similarity"
                                class="absolute bottom-2 left-2 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-indigo-600 text-white" style="display:none"
                                x-text="Math.round(product.similarity * 100) + '% match'"></span>
                        </div>
                        <div class="p-3 flex-1 flex flex-col">
                            <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-1.5" x-text="product.title"></h3>
                            <div class="flex items-baseline gap-1.5">
                                <p class="text-lg font-bold text-indigo-600 leading-tight" x-text="formatPrice(product.price_amount, product.currency)"></p>
                                <p x-show="product.original_price_amount && product.original_price_amount > product.price_amount"
                                    class="text-xs text-gray-400 line-through" style="display:none"
                                    x-text="formatPrice(product.original_price_amount, product.currency)"></p>
                            </div>
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
                <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1" data-i18n="common.previous"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <span class="text-sm text-gray-600 px-3">
                    Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span>
                </span>
                <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page" data-i18n="common.next"
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
            brands: [],
            shops: [],
            banners: [],
            bannerIndex: 0,
            bannerTimer: null,
            trending: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            visualSearching: false,
            visualSearchActive: false,
            visualDragging: false,
            filters: {
                search: '',
                category_id: '',
                sub_category_id: '',
                brand_id: '',
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
                if (params.get('brand')) this.filters.brand_id = params.get('brand');
                if (params.get('city')) this.filters.location_city = params.get('city');
                if (params.get('sort')) this.filters.sort = params.get('sort');
                await Promise.all([
                    this.fetchProducts(),
                    this.fetchCategories(),
                    this.fetchConditions(),
                    this.fetchBrands(),
                    this.fetchBanners(),
                    this.fetchTrending(),
                    this.fetchShops(),
                ]);
                if (window.__pendingVisualSearchFile) {
                    const file = window.__pendingVisualSearchFile;
                    window.__pendingVisualSearchFile = null;
                    await this.searchByPhotoFile(file);
                }
            },
            selectBrand(id) {
                this.filters.brand_id = (this.filters.brand_id == id) ? '' : id;
                this.syncUrl();
                this.resetAndFetch();
            },
            async fetchBrands() {
                try {
                    const { data } = await window.api.get('/brands');
                    this.brands = data.brands || [];
                } catch {}
            },
            async fetchBanners() {
                try {
                    const { data } = await window.api.get('/promo-banners');
                    this.banners = data.promo_banners || [];
                    if (this.banners.length > 1) {
                        clearInterval(this.bannerTimer);
                        this.bannerTimer = setInterval(() => {
                            this.bannerIndex = (this.bannerIndex + 1) % this.banners.length;
                        }, 5000);
                    }
                } catch {}
            },
            async fetchTrending() {
                try {
                    const { data } = await window.api.get('/products', { params: { featured: 1, per_page: 12, sort: 'featured' } });
                    this.trending = data.products || [];
                } catch {}
            },
            async fetchShops() {
                try {
                    const { data } = await window.api.get('/stores', { params: { per_page: 12, sort: 'followers' } });
                    this.shops = data.stores || [];
                } catch {}
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
                if (this.filters.brand_id) p.set('brand', this.filters.brand_id);
                else p.delete('brand');
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
                this.visualSearchActive = false;
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
            async searchByPhoto(event) {
                const file = event.target.files?.[0];
                event.target.value = '';
                await this.searchByPhotoFile(file);
            },
            async searchByPhotoFile(file) {
                if (!file || !file.type?.startsWith('image/')) return;

                this.visualSearching = true;
                this.visualSearchActive = true;
                this.loading = true;
                this.visualDragging = false;
                try {
                    const fd = new FormData();
                    fd.append('image', file);
                    fd.append('limit', 24);

                    const { data } = await window.api.post('/search/visual', fd, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    });

                    this.products = data.products || [];
                    this.meta = {
                        current_page: 1,
                        last_page: 1,
                        per_page: this.products.length,
                        total: data.meta?.total ?? this.products.length,
                    };
                    this.visualSearchActive = true;
                    if (this.products.length === 0) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'No visual matches found for this image.' } }));
                    }
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.visualSearching = false;
                    this.loading = false;
                }
            },
            hasVisualImage(dataTransfer) {
                if (!dataTransfer) return false;
                return Array.from(dataTransfer.items || []).some(item => item.kind === 'file' && item.type.startsWith('image/'))
                    || Array.from(dataTransfer.files || []).some(file => file.type.startsWith('image/'));
            },
            firstVisualImage(files) {
                return Array.from(files || []).find(file => file.type?.startsWith('image/'));
            },
            firstVisualImageItem(items) {
                const item = Array.from(items || []).find(entry => entry.kind === 'file' && entry.type?.startsWith('image/'));
                return item ? item.getAsFile() : null;
            },
            handleVisualPaste(event) {
                const file = this.firstVisualImage(event.clipboardData?.files)
                    || this.firstVisualImageItem(event.clipboardData?.items);
                if (!file) return;
                event.preventDefault();
                this.searchByPhotoFile(file);
            },
            handleVisualDragEnter(event) {
                if (!this.hasVisualImage(event.dataTransfer)) return;
                event.preventDefault();
                this.visualDragging = true;
            },
            handleVisualDragOver(event) {
                if (!this.hasVisualImage(event.dataTransfer)) return;
                event.preventDefault();
                event.dataTransfer.dropEffect = 'copy';
                this.visualDragging = true;
            },
            handleVisualDragLeave(event) {
                if (event.clientX > 0 && event.clientY > 0 && event.clientX < window.innerWidth && event.clientY < window.innerHeight) {
                    return;
                }
                this.visualDragging = false;
            },
            handleVisualDrop(event) {
                const file = this.firstVisualImage(event.dataTransfer?.files);
                if (!file) {
                    this.visualDragging = false;
                    return;
                }
                event.preventDefault();
                this.searchByPhotoFile(file);
            },
            clearVisualSearch() {
                this.visualSearchActive = false;
                this.resetAndFetch();
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
                    search: '', category_id: '', sub_category_id: '', brand_id: '', product_condition_id: '',
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
