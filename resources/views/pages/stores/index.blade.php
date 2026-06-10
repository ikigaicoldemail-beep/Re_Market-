@extends('layouts.app')

@section('title', 'Browse Shops')

@section('content')
@include('components.toast')

<div x-data="storesIndex()" x-init="init">
    {{-- Hero --}}
    <div class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <h1 class="text-3xl font-semibold" data-i18n="stores.title">Discover Shops</h1>
            <p class="text-indigo-100 mt-2" data-i18n="stores.subtitle">Browse independent sellers and explore their full catalogue.</p>

            <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="relative sm:col-span-2">
                    <svg class="w-4 h-4 absolute left-3 top-3 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="filters.search" @keydown.enter="applyFilters()" placeholder="Search shops..." data-i18n="stores.search_placeholder" data-i18n-placeholder
                        class="w-full pl-9 pr-4 py-2 rounded-lg bg-white/15 placeholder-indigo-200 text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-white/50">
                </div>
                <input type="text" x-model="filters.city" @keydown.enter="applyFilters()" placeholder="City" data-i18n="stores.city_placeholder" data-i18n-placeholder
                    class="px-3 py-2 rounded-lg bg-white/15 placeholder-indigo-200 text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-white/50">
                <select x-model="filters.sort" @change="applyFilters()"
                    class="px-3 py-2 rounded-lg bg-white/15 text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-white/50">
                    <option value="latest" class="text-gray-900" data-i18n="stores.sort_latest">Newest</option>
                    <option value="followers" class="text-gray-900" data-i18n="stores.sort_followers">Most followers</option>
                    <option value="name" class="text-gray-900" data-i18n="stores.sort_name">Name (A → Z)</option>
                    <option value="nearest" class="text-gray-900">Nearest to me</option>
                </select>
            </div>

            {{-- Near me --}}
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button type="button" @click="nearMe ? clearNearMe() : enableNearMe()" :disabled="locating"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition disabled:opacity-60"
                    :class="nearMe ? 'bg-white text-indigo-700 border-white shadow-sm' : 'bg-white/15 text-white border-white/25 hover:bg-white/25'">
                    <svg class="w-4 h-4" :class="locating && 'animate-pulse'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-text="locating ? 'Finding your location…' : (nearMe ? 'Showing nearby — clear' : 'Stores near me')"></span>
                </button>
                <span x-show="nearMe" class="text-sm text-indigo-100" style="display:none">Sorted by distance from your location.</span>
            </div>
        </div>
    </div>

    {{-- Results --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900" data-i18n="stores.all_shops">All shops</h2>
            <span class="text-sm text-gray-500" x-text="meta.total + ' shop' + (meta.total === 1 ? '' : 's')"></span>
        </div>

        <div x-show="loading" class="text-center py-20 text-gray-500" data-i18n="stores.loading">Loading shops...</div>

        <div x-show="!loading && stores.length === 0" class="text-center py-20 bg-white rounded-xl border border-gray-200" style="display:none">
            <p class="text-gray-500" data-i18n="stores.empty">No shops match your filters.</p>
        </div>

        <div x-show="!loading && stores.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5" style="display:none">
            <template x-for="store in stores" :key="store.id">
                <a :href="'/stores/' + store.id"
                    class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition group">
                    {{-- Banner --}}
                    <div class="relative h-24 bg-gradient-to-br from-indigo-500 to-purple-500 overflow-hidden">
                        <template x-if="store.banner_url">
                            <img :src="store.banner_url" :alt="store.name" class="absolute inset-0 w-full h-full object-cover">
                        </template>
                    </div>

                    {{-- Body --}}
                    <div class="p-4 pt-0">
                        <div class="-mt-8 mb-3 w-16 h-16 rounded-2xl bg-white border-4 border-white shadow flex items-center justify-center overflow-hidden">
                            <template x-if="store.logo_url">
                                <img :src="store.logo_url" :alt="store.name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!store.logo_url">
                                <span class="text-2xl font-bold text-indigo-600" x-text="store.name?.charAt(0)?.toUpperCase()"></span>
                            </template>
                        </div>
                        <h3 class="font-semibold text-gray-900 flex items-center gap-1 truncate">
                            <span x-text="store.name"></span>
                            <svg x-show="store.is_verified" class="w-4 h-4 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20" style="display:none">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-show="store.city || store.country_code" x-text="[store.city, store.country_code].filter(Boolean).join(', ')" style="display:none"></p>
                        <p class="text-xs font-medium text-indigo-600 mt-1 inline-flex items-center gap-1" x-show="store.distance_km != null" style="display:none">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-text="formatDistance(store.distance_km)"></span>
                        </p>
                        <p class="text-sm text-gray-600 mt-2 line-clamp-2 min-h-[2.5rem]" x-text="store.description || 'No description.'"></p>
                        <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                            <span><strong class="text-gray-900" x-text="store.products_count ?? 0"></strong> product<span x-show="(store.products_count ?? 0) !== 1" style="display:none">s</span></span>
                            <span><strong class="text-gray-900" x-text="store.followers_count ?? 0"></strong> follower<span x-show="(store.followers_count ?? 0) !== 1" style="display:none">s</span></span>
                        </div>
                    </div>
                </a>
            </template>
        </div>

        {{-- Pagination --}}
        <div x-show="!loading && meta.last_page > 1" class="flex items-center justify-center gap-2 mt-8" style="display:none">
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

@push('scripts')
<script>
    function storesIndex() {
        return {
            stores: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            page: 1,
            loading: true,
            locating: false,
            nearMe: false,
            filters: { search: '', city: '', sort: 'latest', lat: null, lng: null },
            async init() {
                await this.fetch();
            },
            applyFilters() {
                this.page = 1;
                // Picking "Nearest to me" without a location yet → ask for it first.
                if (this.filters.sort === 'nearest' && this.filters.lat == null) {
                    this.enableNearMe();
                    return;
                }
                this.fetch();
            },
            enableNearMe() {
                if (!navigator.geolocation) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Your browser does not support location.' } }));
                    return;
                }
                this.locating = true;
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.filters.lat = pos.coords.latitude;
                        this.filters.lng = pos.coords.longitude;
                        this.filters.sort = 'nearest';
                        this.nearMe = true;
                        this.locating = false;
                        this.page = 1;
                        this.fetch();
                    },
                    () => {
                        this.locating = false;
                        if (this.filters.sort === 'nearest') this.filters.sort = 'latest';
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Could not get your location. Please allow location access and try again.' } }));
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
                );
            },
            clearNearMe() {
                this.nearMe = false;
                this.filters.lat = null;
                this.filters.lng = null;
                if (this.filters.sort === 'nearest') this.filters.sort = 'latest';
                this.page = 1;
                this.fetch();
            },
            formatDistance(km) {
                if (km == null) return '';
                return km < 1 ? `${Math.round(km * 1000)} m away` : `${km} km away`;
            },
            async fetch() {
                this.loading = true;
                try {
                    const params = { page: this.page, sort: this.filters.sort };
                    if (this.filters.search) params.search = this.filters.search;
                    if (this.filters.city) params.city = this.filters.city;
                    if (this.filters.lat != null && this.filters.lng != null) {
                        params.lat = this.filters.lat;
                        params.lng = this.filters.lng;
                    }
                    const { data } = await window.api.get('/stores', { params });
                    this.stores = data.stores || [];
                    this.meta = data.meta || this.meta;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            goToPage(page) {
                this.page = page;
                this.fetch();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
        };
    }
</script>
@endpush
@endsection
