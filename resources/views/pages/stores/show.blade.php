@extends('layouts.app')

@section('title', 'Store')

@section('content')
@include('components.toast')

<div x-data="storePage()" x-init="init">
    <div x-show="loading" class="text-center py-20 text-gray-500">Loading store...</div>

    <div x-show="error" class="text-center py-20" style="display:none">
        <p class="text-red-600 mb-4" x-text="error"></p>
        <a href="/" class="text-indigo-600 hover:text-indigo-700">Back to browse</a>
    </div>

    <div x-show="store && !loading" style="display:none">
        {{-- Store header --}}
        <div class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center text-3xl font-bold">
                        <span x-text="store?.name?.charAt(0)?.toUpperCase()"></span>
                    </div>
                    <div>
                        <h1 class="text-3xl font-semibold flex items-center gap-2">
                            <span x-text="store?.name"></span>
                            <svg x-show="store?.is_verified" class="w-5 h-5 text-blue-300" fill="currentColor" viewBox="0 0 20 20" style="display:none">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </h1>
                        <p class="text-indigo-100 text-sm mt-1" x-text="[store?.city, store?.state, store?.country_code].filter(Boolean).join(', ')"></p>
                    </div>
                </div>
                <p x-show="store?.description" class="mt-4 text-indigo-50 max-w-3xl" x-text="store?.description" style="display:none"></p>

                <div class="flex flex-wrap gap-4 mt-6 text-sm">
                    <span x-show="store?.contact_email" class="flex items-center gap-2 bg-white/10 rounded-full px-3 py-1" style="display:none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <a :href="'mailto:' + store?.contact_email" x-text="store?.contact_email"></a>
                    </span>
                    <span x-show="store?.contact_phone" class="flex items-center gap-2 bg-white/10 rounded-full px-3 py-1" style="display:none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span x-text="store?.contact_phone"></span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Products --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Products</h2>
                <span class="text-sm text-gray-500" x-text="meta.total + ' items'"></span>
            </div>

            <div x-show="products.length === 0" class="text-center py-20 bg-white rounded-xl border border-gray-200" style="display:none">
                <p class="text-gray-500">This store has no products listed yet.</p>
            </div>

            <div x-show="products.length > 0" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4" style="display:none">
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
                        </div>
                    </a>
                </template>
            </div>

            <div x-show="meta.last_page > 1" class="flex items-center justify-center gap-2 mt-8" style="display:none">
                <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">Previous</button>
                <span class="text-sm text-gray-600 px-3">
                    Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span>
                </span>
                <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
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
            primaryImage(product) {
                if (product.images && product.images.length > 0) {
                    const primary = product.images.find(i => i.is_primary) || product.images[0];
                    return primary.url;
                }
                return 'https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image';
            },
            async init() {
                const segments = window.location.pathname.split('/').filter(Boolean);
                this.storeId = parseInt(segments[segments.length - 1]);
                await this.fetch();
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/stores/' + this.storeId + '/products', { params: { page: this.page } });
                    this.store = data.store;
                    this.products = data.products || [];
                    this.meta = data.meta || this.meta;
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
        };
    }
</script>
@endpush
@endsection
