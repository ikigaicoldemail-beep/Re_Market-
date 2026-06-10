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
                <button type="button" @click="nearMe ? clearNearMe() : openLocationPicker()" :disabled="locating"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition disabled:opacity-60"
                    :class="nearMe ? 'bg-white text-indigo-700 border-white shadow-sm' : 'bg-white/15 text-white border-white/25 hover:bg-white/25'">
                    <svg class="w-4 h-4" :class="locating && 'animate-pulse'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-text="locating ? 'Finding your location...' : (nearMe ? 'Showing nearby - clear' : 'Stores near me')"></span>
                </button>
                <span x-show="nearMe" class="text-sm text-indigo-100" style="display:none">
                    Sorted from <span x-text="locationLabel"></span>.
                </span>
            </div>
        </div>
    </div>

    {{-- Location picker --}}
    <div x-show="locationPickerOpen"
        x-transition.opacity
        @keydown.escape.window="closeLocationPicker()"
        class="fixed inset-0 z-50 bg-gray-950/55 backdrop-blur-sm flex items-center justify-center px-4 py-6"
        style="display:none">
        <div @click.outside="closeLocationPicker()"
            class="w-full max-w-3xl bg-white rounded-lg shadow-2xl border border-gray-200 overflow-hidden">
            <div class="flex items-start justify-between gap-4 px-5 py-4 border-b border-gray-100">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Choose your location</h2>
                    <p class="text-sm text-gray-500 mt-1">Allow browser location or pick an approximate point yourself.</p>
                </div>
                <button type="button" @click="closeLocationPicker()"
                    class="w-9 h-9 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-900 flex items-center justify-center"
                    aria-label="Close location picker">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="grid md:grid-cols-[260px_1fr] gap-0">
                <div class="p-5 border-b md:border-b-0 md:border-r border-gray-100">
                    <button type="button" @click="useBrowserLocation()" :disabled="locating"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 text-white px-4 py-2.5 text-sm font-semibold hover:bg-indigo-700 disabled:opacity-60">
                        <svg class="w-4 h-4" :class="locating && 'animate-pulse'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v3m0 14v3m10-10h-3M5 12H2m16.95-6.95l-2.12 2.12M7.17 16.83l-2.12 2.12m0-13.9l2.12 2.12m9.66 9.66l2.12 2.12"/>
                        </svg>
                        <span x-text="locating ? 'Waiting for permission...' : 'Use browser location'"></span>
                    </button>

                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Quick picks</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="place in locationPresets" :key="place.name">
                                <button type="button" @click="setMapLocation(place.name, place.lat, place.lng)"
                                    :class="manualLocation.label === place.name ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                                    class="px-3 py-1.5 rounded-full border text-xs font-medium transition"
                                    x-text="place.name"></button>
                            </template>
                        </div>
                    </div>

                    <div class="mt-5 rounded-lg bg-gray-50 border border-gray-200 p-3">
                        <p class="text-xs text-gray-500">Selected point</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1" x-text="manualLocation.label"></p>
                        <p class="text-xs text-gray-500 mt-1">
                            <span x-text="manualLocation.lat.toFixed(4)"></span>,
                            <span x-text="manualLocation.lng.toFixed(4)"></span>
                        </p>
                    </div>
                </div>

                <div class="p-5">
                    <div class="relative h-80 rounded-lg overflow-hidden border border-gray-200 bg-gray-100">
                        <div x-ref="leafletMap" class="absolute inset-0 z-0"></div>

                        <div x-show="!mapLoading && !mapError"
                            class="absolute left-3 bottom-3 max-w-[calc(100%-1.5rem)] rounded-lg bg-white/95 shadow-sm border border-gray-200 px-3 py-2"
                            style="display:none">
                            <p class="text-[11px] uppercase tracking-wide font-semibold text-gray-500">Selected point</p>
                            <p class="text-sm font-semibold text-gray-900 truncate" x-text="manualLocation.label"></p>
                            <p class="text-xs text-gray-500">
                                <span x-text="manualLocation.lat.toFixed(4)"></span>,
                                <span x-text="manualLocation.lng.toFixed(4)"></span>
                            </p>
                        </div>

                        <div x-show="mapLoading"
                            class="absolute inset-0 bg-white/85 flex items-center justify-center text-sm font-medium text-gray-600"
                            style="display:none">
                            Loading map...
                        </div>

                        <div x-show="mapError"
                            class="absolute inset-0 bg-white flex items-center justify-center p-6 text-center"
                            style="display:none">
                            <div class="max-w-sm">
                                <p class="text-sm font-semibold text-gray-900" x-text="mapErrorTitle"></p>
                                <p class="text-sm text-gray-500 mt-1" x-text="mapErrorMessage"></p>
                                <p class="text-xs text-gray-400 mt-3">You can still use quick picks and browser location.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4">
                        <p class="text-xs text-gray-500">Click the map or drag the pin to choose an approximate location.</p>
                        <div class="flex gap-2">
                            <button type="button" @click="closeLocationPicker()"
                                class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="button" @click="applyManualLocation()"
                                class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Use this point</button>
                        </div>
                    </div>
                </div>
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

        <div x-show="!loading && stores.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3" style="display:none">
            <template x-for="store in stores" :key="store.id">
                <a :href="'/stores/' + store.id"
                    class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-sm hover:border-indigo-200 transition group">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-100 to-pink-100 border border-gray-100 shadow-sm flex items-center justify-center overflow-hidden shrink-0">
                            <template x-if="store.logo_url">
                                <img :src="store.logo_url" :alt="store.name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!store.logo_url">
                                <span class="text-lg font-bold text-indigo-600" x-text="store.name?.charAt(0)?.toUpperCase()"></span>
                            </template>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <h3 class="font-semibold text-gray-900 truncate group-hover:text-indigo-700 transition" x-text="store.name"></h3>
                                <svg x-show="store.is_verified" class="w-4 h-4 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20" style="display:none">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5 truncate" x-show="store.city || store.country_code" x-text="[store.city, store.country_code].filter(Boolean).join(', ')" style="display:none"></p>
                            <p class="text-xs font-medium text-indigo-600 mt-1 inline-flex items-center gap-1" x-show="store.distance_km != null" style="display:none">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span x-text="formatDistance(store.distance_km)"></span>
                            </p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mt-3 line-clamp-2 min-h-[2.5rem]" x-text="store.description || 'No description.'"></p>

                    <div class="flex items-center gap-4 mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                        <span><strong class="text-gray-900" x-text="store.products_count ?? 0"></strong> product<span x-show="(store.products_count ?? 0) !== 1" style="display:none">s</span></span>
                        <span><strong class="text-gray-900" x-text="store.followers_count ?? 0"></strong> follower<span x-show="(store.followers_count ?? 0) !== 1" style="display:none">s</span></span>
                        <span class="ml-auto text-indigo-600 font-medium opacity-0 group-hover:opacity-100 transition">View</span>
                    </div>

                    <template x-if="store.banner_url">
                        <div class="mt-3 h-1 rounded-full overflow-hidden bg-gray-100">
                            <img :src="store.banner_url" :alt="store.name" class="w-full h-full object-cover">
                        </div>
                    </template>
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
            locationPickerOpen: false,
            locationLabel: 'your location',
            leafletMap: null,
            leafletMarker: null,
            mapLoading: false,
            mapError: false,
            mapErrorTitle: 'Map could not load.',
            mapErrorMessage: 'Leaflet or OpenStreetMap tiles are not available right now.',
            manualLocation: { label: 'Phnom Penh', lat: 11.5564, lng: 104.9282 },
            locationPresets: [
                { name: 'Phnom Penh', lat: 11.5564, lng: 104.9282 },
                { name: 'Siem Reap', lat: 13.3671, lng: 103.8448 },
                { name: 'Battambang', lat: 13.0957, lng: 103.2022 },
                { name: 'Kampong Cham', lat: 12.0000, lng: 105.4500 },
                { name: 'Sihanoukville', lat: 10.6253, lng: 103.5234 },
                { name: 'Kandal', lat: 11.4833, lng: 104.9500 },
                { name: 'Kampot', lat: 10.6104, lng: 104.1814 },
                { name: 'Kep', lat: 10.4829, lng: 104.3167 },
            ],
            filters: { search: '', city: '', sort: 'latest', lat: null, lng: null },
            async init() {
                await this.fetch();
                this.openRequestedLocationPicker();
            },
            applyFilters() {
                this.page = 1;
                // Picking "Nearest to me" without a location yet asks for a privacy choice first.
                if (this.filters.sort === 'nearest' && this.filters.lat == null) {
                    this.openLocationPicker();
                    return;
                }
                this.fetch();
            },
            openLocationPicker() {
                this.locationPickerOpen = true;
                this.$nextTick(() => this.initLeafletMap());
            },
            openRequestedLocationPicker() {
                const params = new URLSearchParams(window.location.search);
                if (params.get('near_me') !== '1') {
                    return;
                }

                const lat = Number(params.get('lat'));
                const lng = Number(params.get('lng'));
                if (Number.isFinite(lat) && Number.isFinite(lng)) {
                    this.manualLocation = {
                        label: params.get('label') || 'Selected point',
                        lat,
                        lng,
                    };
                }

                this.openLocationPicker();
                window.history.replaceState({}, '', window.location.pathname);
            },
            closeLocationPicker() {
                this.locationPickerOpen = false;
            },
            useBrowserLocation() {
                if (!navigator.geolocation) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Your browser does not support location. Pick a point on the map instead.' } }));
                    return;
                }
                this.locating = true;
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.setNearbyLocation(pos.coords.latitude, pos.coords.longitude, 'your browser location');
                        this.locating = false;
                        this.closeLocationPicker();
                    },
                    () => {
                        this.locating = false;
                        if (this.filters.sort === 'nearest') this.filters.sort = 'latest';
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Could not get your location. You can still click the map to choose manually.' } }));
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
                );
            },
            setNearbyLocation(lat, lng, label) {
                this.filters.lat = Number(lat);
                this.filters.lng = Number(lng);
                this.filters.sort = 'nearest';
                this.nearMe = true;
                this.locationLabel = label || 'selected point';
                this.page = 1;
                this.fetch();
            },
            setMapLocation(label, lat, lng) {
                this.manualLocation = { label, lat: Number(lat), lng: Number(lng) };
                this.syncLeafletMarker(true);
            },
            applyManualLocation() {
                this.setNearbyLocation(this.manualLocation.lat, this.manualLocation.lng, this.manualLocation.label);
                this.closeLocationPicker();
            },
            async initLeafletMap() {
                if (!this.$refs.leafletMap || this.leafletMap) {
                    if (this.leafletMap) {
                        setTimeout(() => {
                            this.leafletMap.invalidateSize();
                            this.leafletMap.setView([this.manualLocation.lat, this.manualLocation.lng]);
                        }, 50);
                    }
                    return;
                }

                this.mapError = false;
                this.mapLoading = true;

                try {
                    if (!window.L) {
                        this.showMapError(
                            'Map library is not ready.',
                            'Refresh the page and try again. Browser location and quick picks still work.'
                        );
                        return;
                    }

                    const center = [this.manualLocation.lat, this.manualLocation.lng];
                    this.leafletMap = window.L.map(this.$refs.leafletMap, {
                        center,
                        zoom: 7,
                        zoomControl: true,
                        attributionControl: true,
                    });
                    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(this.leafletMap);

                    this.leafletMarker = window.L.marker(center, {
                        draggable: true,
                        title: 'Your selected location',
                    }).addTo(this.leafletMap);

                    this.leafletMap.on('click', (event) => {
                        this.setManualLocation('Custom map point', event.latlng.lat, event.latlng.lng);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Selected map point updated.' } }));
                    });
                    this.leafletMarker.on('dragend', (event) => {
                        const position = event.target.getLatLng();
                        this.setManualLocation('Custom map point', position.lat, position.lng);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Selected map point updated.' } }));
                    });

                    setTimeout(() => this.leafletMap.invalidateSize(), 50);
                } catch (error) {
                    this.showMapError(
                        'OpenStreetMap could not load.',
                        'Check your internet connection. Browser location and quick picks still work.'
                    );
                } finally {
                    this.mapLoading = false;
                }
            },
            showMapError(title, message) {
                this.mapLoading = false;
                this.mapError = true;
                this.mapErrorTitle = title;
                this.mapErrorMessage = message;
            },
            setManualLocation(label, lat, lng) {
                this.manualLocation = { label, lat: Number(lat), lng: Number(lng) };
                this.syncLeafletMarker(false);
            },
            syncLeafletMarker(recenter = false) {
                if (!this.leafletMap || !this.leafletMarker) {
                    return;
                }

                const position = [this.manualLocation.lat, this.manualLocation.lng];
                this.leafletMarker.setLatLng(position);

                if (recenter) {
                    this.leafletMap.panTo(position);
                }
            },
            clearNearMe() {
                this.nearMe = false;
                this.locationPickerOpen = false;
                this.locationLabel = 'your location';
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
