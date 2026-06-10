@extends('layouts.app')

@section('title', 'Store')

@section('content')
@include('components.toast')

<div x-data="storePage()" x-init="init">
    <div x-show="loading" class="text-center py-20 text-gray-500" data-i18n="stores.loading_store">Loading store...</div>

    <div x-show="error" class="text-center py-20" style="display:none">
        <p class="text-red-600 mb-4" x-text="error"></p>
        <a href="/" class="text-indigo-600 hover:text-indigo-700" data-i18n="product.back_to_browse">Back to browse</a>
    </div>

    <div x-show="store && !loading" style="display:none">
        {{-- Banner --}}
        <div x-show="store?.banner_url"
            class="relative h-40 sm:h-56 bg-gradient-to-br from-indigo-600 to-purple-600 overflow-hidden"
            style="display:none">
            <img :src="store?.banner_url" :alt="store?.name + ' banner'" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        </div>

        {{-- Store header --}}
        <div class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="flex items-start gap-4 flex-wrap">
                    <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center text-3xl font-bold shrink-0 overflow-hidden">
                        <template x-if="store?.logo_url">
                            <img :src="store.logo_url" :alt="store.name" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!store?.logo_url">
                            <span x-text="store?.name?.charAt(0)?.toUpperCase()"></span>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl font-semibold flex items-center gap-2">
                            <span x-text="store?.name"></span>
                            <svg x-show="store?.is_verified" class="w-5 h-5 text-blue-300" fill="currentColor" viewBox="0 0 20 20" style="display:none">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </h1>
                        <p class="text-indigo-100 text-sm mt-1" x-text="[store?.city, store?.state, store?.country_code].filter(Boolean).join(', ')"></p>
                        <p class="text-indigo-100 text-sm mt-1">
                            <span class="font-semibold text-white" x-text="store?.followers_count ?? 0"></span> follower<span x-show="(store?.followers_count ?? 0) !== 1" style="display:none">s</span>
                        </p>
                    </div>
                    <div x-show="isOwnStore" class="flex flex-wrap gap-2" style="display:none">
                        <a href="/me/products/new"
                            class="text-sm font-medium px-5 py-2 rounded-lg transition bg-white text-indigo-700 hover:bg-indigo-50 inline-flex items-center gap-1.5">
                            <x-heroicon-o-plus class="w-4 h-4"/>
                            Add item
                        </a>
                        <a href="/social/scheduled-posts"
                            class="text-sm font-medium px-5 py-2 rounded-lg transition bg-white/15 text-white hover:bg-white/25 inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                            Add post
                        </a>
                        <a href="/me/store"
                            class="text-sm font-medium px-5 py-2 rounded-lg transition bg-white/15 text-white hover:bg-white/25 inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit store
                        </a>
                    </div>
                    <div x-show="canFollow" style="display:none">
                        <button @click="toggleFollow()" :disabled="followBusy"
                            :class="store?.is_following ? 'bg-white/15 hover:bg-white/25' : 'bg-white text-indigo-700 hover:bg-indigo-50'"
                            class="text-sm font-medium px-5 py-2 rounded-lg transition disabled:opacity-50">
                            <span x-show="!followBusy" class="inline-flex items-center gap-1.5">
                                <template x-if="store?.is_following">
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-heroicon-s-check class="w-4 h-4"/>Following
                                    </span>
                                </template>
                                <template x-if="!store?.is_following">
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-heroicon-o-plus class="w-4 h-4"/>Follow
                                    </span>
                                </template>
                            </span>
                            <span x-show="followBusy" style="display:none">...</span>
                        </button>
                    </div>
                </div>
                <p x-show="store?.description" class="mt-4 text-indigo-50 max-w-3xl" x-text="store?.description" style="display:none"></p>

                <div class="flex flex-wrap gap-3 mt-6 text-sm">
                    <span x-show="store?.contact_email" class="flex items-center gap-2 bg-white/10 rounded-full px-3 py-1" style="display:none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <a :href="'mailto:' + store?.contact_email" x-text="store?.contact_email"></a>
                    </span>
                    <span x-show="store?.contact_phone" class="flex items-center gap-2 bg-white/10 rounded-full px-3 py-1" style="display:none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span x-text="store?.contact_phone"></span>
                    </span>
                    <a x-show="store?.telegram_url" :href="store?.telegram_url" target="_blank" rel="noopener"
                        class="flex items-center gap-2 bg-sky-500 hover:bg-sky-600 rounded-full px-3 py-1 transition" style="display:none">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M21.426 2.574L2.13 9.97c-.948.36-.943.86-.171 1.094l4.94 1.541 11.43-7.214c.54-.328 1.034-.151.63.21l-9.258 8.36-.357 5.346c.357 0 .515-.164.715-.358l1.717-1.667 3.563 2.633c.658.363 1.13.175 1.292-.611l2.342-10.99c.243-1.122-.382-1.587-1.547-1.14z"/></svg>
                        Telegram
                    </a>
                    <a x-show="store?.messenger_url" :href="store?.messenger_url" target="_blank" rel="noopener"
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 rounded-full px-3 py-1 transition" style="display:none">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.145 2 11.243c0 2.835 1.387 5.36 3.55 7.06V22l3.245-1.78c.864.24 1.78.367 2.705.367 5.523 0 10-4.145 10-9.243C22 6.145 17.523 2 12 2zm.987 12.421l-2.55-2.717-4.937 2.717 5.43-5.764 2.61 2.717 4.876-2.717-5.43 5.764z"/></svg>
                        Messenger
                    </a>
                </div>
            </div>
        </div>

        {{-- Map --}}
        <div x-show="store?.latitude && store?.longitude" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6" style="display:none">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide" data-i18n="stores.location">Location</h2>
                    <p class="text-xs text-gray-500 mt-1">This map shows the store address. Use the buttons to navigate or search nearby shops.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a :href="'https://www.google.com/maps/dir/?api=1&destination=' + store?.latitude + ',' + store?.longitude"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                        <x-heroicon-o-map-pin class="w-4 h-4"/>
                        Directions
                    </a>
                    <button type="button"
                        @click="openNearbyPicker()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4"/>
                        Stores near me
                    </button>
                </div>
            </div>
            <div class="rounded-xl overflow-hidden border border-gray-200 bg-white">
                <div x-ref="storeMap" class="h-80 w-full bg-gray-100"></div>
                <div class="px-4 py-2 text-xs text-gray-500 border-t border-gray-100 flex items-center justify-between">
                    <span x-text="(store?.address_line || '') + (store?.address_line && store?.city ? ' · ' : '') + (store?.city || '')"></span>
                    <div class="flex items-center gap-3">
                        <a :href="openStreetMapUrl()" target="_blank" rel="noopener"
                            class="text-gray-600 hover:text-gray-900 font-medium">Open map</a>
                        <a :href="'https://www.google.com/maps/dir/?api=1&destination=' + store?.latitude + ',' + store?.longitude" target="_blank" rel="noopener"
                            class="text-indigo-600 hover:text-indigo-700 font-medium" data-i18n="stores.get_directions">Get directions →</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900" data-i18n="stores.products">Products</h2>
                <span class="text-sm text-gray-500" x-text="meta.total + ' ' + (window.t ? window.t('common.items') : 'items')"></span>
            </div>

            <div x-show="products.length === 0" class="text-center py-20 bg-white rounded-xl border border-gray-200" style="display:none">
                <p class="text-gray-500" data-i18n="stores.no_products">This store has no products listed yet.</p>
            </div>

            <div x-show="products.length > 0" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4" style="display:none">
                <template x-for="product in products" :key="product.id">
                    <a :href="'/products/' + product.id"
                        class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition group">
                        <div class="aspect-square bg-gray-100 overflow-hidden relative">
                            <img :src="primaryImage(product)" :alt="product.title" loading="lazy"
                                onerror="this.src='https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image'"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <span x-show="product.condition"
                                class="absolute top-2 left-2 text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                :class="conditionChipClasses(product.condition?.color)"
                                x-text="product.condition?.name" style="display:none"></span>
                        </div>
                        <div class="p-3">
                            <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-1.5" x-text="product.title"></h3>
                            <p class="text-lg font-bold text-indigo-600 leading-tight" x-text="formatPrice(product.price_amount, product.currency)"></p>
                            <p class="text-xs text-gray-400 mt-1" x-show="product.published_at || product.created_at"
                               x-text="formatRelativeTime(product.published_at || product.created_at)" style="display:none"></p>
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
</div>

@push('scripts')
<script>
    function storePage() {
        return {
            storeId: null,
            store: null,
            products: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            page: 1,
            loading: true,
            error: '',
            followBusy: false,
            storeMap: null,
            storeMarker: null,
            get isOwnStore() {
                const me = window.auth.user();
                return !!me && this.store && me.id === this.store?.seller?.id;
            },
            get canFollow() {
                const me = window.auth.user();
                return !!me && this.store && me.id !== this.store?.seller?.id;
            },
            primaryImage(product) {
                if (product.images && product.images.length > 0) {
                    const primary = product.images.find(i => i.is_primary) || product.images[0];
                    return primary.urls?.card_webp || primary.urls?.card || primary.url;
                }
                return 'https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image';
            },
            async init() {
                const routeStoreId = @json($id);
                if (routeStoreId) {
                    this.storeId = Number(routeStoreId);
                    await this.fetch();
                    return;
                }

                const segments = window.location.pathname.split('/').filter(Boolean);
                const last = segments[segments.length - 1];
                const parsed = parseInt(last);
                if (!Number.isNaN(parsed)) {
                    this.storeId = parsed;
                } else {
                    // last segment is likely a slug; try searching stores by name/slug
                    try {
                        const { data } = await window.api.get('/stores', { params: { search: last, per_page: 1 } });
                        const found = (data.stores || [])[0] || null;
                        if (found) {
                            this.storeId = found.id;
                        } else {
                            this.error = 'Store not found.';
                            this.loading = false;
                            return;
                        }
                    } catch (e) {
                        this.error = window.extractApiError(e) || 'Store not found.';
                        this.loading = false;
                        return;
                    }
                }

                await this.fetch();
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/stores/' + this.storeId + '/products', { params: { page: this.page } });
                    this.store = data.store;
                    this.products = data.products || [];
                    this.meta = data.meta || this.meta;
                    this.$nextTick(() => this.initStoreMap());
                } catch (e) {
                    this.error = window.extractApiError(e) || 'Store not found.';
                } finally {
                    this.loading = false;
                }
            },
            goToPage(page) {
                this.page = page;
                this.fetch();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            async toggleFollow() {
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                this.followBusy = true;
                try {
                    const wasFollowing = !!this.store.is_following;
                    const { data } = wasFollowing
                        ? await window.api.delete('/stores/' + this.storeId + '/follow')
                        : await window.api.post('/stores/' + this.storeId + '/follow');
                    this.store = { ...this.store, ...data.store, is_following: data.is_following };
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.followBusy = false;
                }
            },
            openNearbyPicker() {
                window.location.href = '/stores?near_me=1';
            },
            initStoreMap() {
                if (!window.L || !this.$refs.storeMap || !this.store?.latitude || !this.store?.longitude) {
                    return;
                }

                const position = [Number(this.store.latitude), Number(this.store.longitude)];
                if (this.storeMap) {
                    this.storeMap.setView(position, 15);
                    this.storeMarker?.setLatLng(position);
                    setTimeout(() => this.storeMap.invalidateSize(), 50);
                    return;
                }

                this.storeMap = window.L.map(this.$refs.storeMap, {
                    center: position,
                    zoom: 15,
                    scrollWheelZoom: false,
                });
                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(this.storeMap);
                this.storeMarker = window.L.marker(position, {
                    title: this.store.name || 'Store location',
                }).addTo(this.storeMap);
                setTimeout(() => this.storeMap.invalidateSize(), 50);
            },
            openStreetMapUrl() {
                if (!this.store?.latitude || !this.store?.longitude) {
                    return 'https://www.openstreetmap.org';
                }

                const lat = Number(this.store.latitude);
                const lng = Number(this.store.longitude);
                return `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}#map=16/${lat}/${lng}`;
            },
        };
    }
</script>
@endpush
@endsection
