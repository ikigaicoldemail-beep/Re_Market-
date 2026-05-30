@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="wishlistPage()" x-init="fetch">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6" data-i18n="wishlist.title">My Wishlist</h1>

    <div x-show="loading" class="text-center py-20 text-gray-500" data-i18n="wishlist.loading">Loading...</div>

    <div x-show="!loading && products.length === 0" class="text-center py-20 bg-white rounded-xl border border-gray-200" style="display:none">
        <p class="text-gray-500 mb-4" data-i18n="wishlist.empty">Your wishlist is empty.</p>
        <a href="/" class="inline-block bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-indigo-700" data-i18n="wishlist.browse_products">Browse products</a>
    </div>

    <div x-show="!loading && products.length > 0" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4" style="display:none">
        <template x-for="product in products" :key="product.id">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden group">
                <a :href="'/products/' + product.id" class="block">
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
                <div class="p-3 pt-0 flex gap-2">
                    <button @click="chatWithSeller(product)" :disabled="busy === product.id"
                        class="flex-1 bg-indigo-600 text-white text-xs py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 inline-flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span x-text="busy === product.id ? 'Opening...' : 'Chat with Seller'"></span>
                    </button>
                    <button @click="remove(product)" :disabled="busy === product.id"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-xs text-red-600 hover:bg-red-50" data-i18n="wishlist.remove">
                        Remove
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
    function wishlistPage() {
        return {
            products: [],
            loading: true,
            busy: null,
            primaryImage(product) {
                if (product.images && product.images.length > 0) {
                    const primary = product.images.find(i => i.is_primary) || product.images[0];
                    return primary.urls?.card_webp || primary.urls?.card || primary.url;
                }
                return 'https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image';
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/wishlist');
                    this.products = data.products || [];
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            async chatWithSeller(product) {
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=/wishlist';
                    return;
                }
                if (!product.user_id) {
                    window.location.href = '/products/' + product.id;
                    return;
                }
                this.busy = product.id;
                try {
                    const { data } = await window.api.post('/conversations', {
                        recipient_user_id: product.user_id,
                        product_id: product.id,
                    });
                    const convId = data?.conversation?.id;
                    if (convId) window.location.href = '/messages/' + convId;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.busy = null;
                }
            },
            async remove(product) {
                this.busy = product.id;
                try {
                    await window.api.delete('/wishlist/' + product.id);
                    this.products = this.products.filter(p => p.id !== product.id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Removed from wishlist.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.busy = null;
                }
            },
        };
    }
</script>
@endpush
@endsection
