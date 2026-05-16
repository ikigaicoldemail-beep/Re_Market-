@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
@include('components.toast')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="productDetail()" x-init="init">
    <div x-show="loading" class="text-center py-20 text-gray-500">Loading product...</div>

    <div x-show="error" class="text-center py-20" style="display:none">
        <p class="text-red-600 mb-4" x-text="error"></p>
        <a href="/" class="text-indigo-600 hover:text-indigo-700">Back to browse</a>
    </div>

    <div x-show="product && !loading" class="grid lg:grid-cols-2 gap-8" style="display:none">
        {{-- Image Gallery --}}
        <div>
            <div class="aspect-square bg-white rounded-xl overflow-hidden border border-gray-200">
                <img :src="activeImage" :alt="product?.title"
                    onerror="this.src='https://placehold.co/600x600/e5e7eb/9ca3af?text=No+Image'"
                    class="w-full h-full object-cover">
            </div>
            <div class="grid grid-cols-5 gap-2 mt-3" x-show="product?.images?.length > 1" style="display:none">
                <template x-for="img in product.images" :key="img.id">
                    <button @click="activeImage = img.url"
                        :class="activeImage === img.url ? 'ring-2 ring-indigo-600' : 'ring-1 ring-gray-200'"
                        class="aspect-square bg-white rounded-lg overflow-hidden">
                        <img :src="img.url" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>

        {{-- Details --}}
        <div>
            <h1 class="text-3xl font-semibold text-gray-900" x-text="product?.title"></h1>

            <div class="flex items-center gap-3 mt-2">
                <span class="text-sm text-gray-500" x-text="product?.location_city || product?.location_country_code || ''"></span>
                <span x-show="product?.condition" class="text-xs px-2 py-0.5 bg-gray-100 text-gray-700 rounded-full"
                    x-text="product?.condition?.name" style="display:none"></span>
            </div>

            <p class="text-3xl font-bold text-indigo-600 mt-4" x-text="formatPrice(product?.price_amount, product?.currency)"></p>

            <p class="text-sm text-gray-600 mt-1" x-show="product?.stock_quantity > 0" style="display:none">
                <span x-text="product?.stock_quantity"></span> in stock
            </p>
            <p class="text-sm text-red-600 mt-1" x-show="product?.stock_quantity <= 0" style="display:none">Out of stock</p>

            {{-- Seller / Store --}}
            <div class="mt-6 p-4 bg-white rounded-xl border border-gray-200" x-show="product?.store" style="display:none">
                <p class="text-xs text-gray-500 mb-1">Sold by</p>
                <p class="font-medium text-gray-900" x-text="product?.store?.name"></p>
            </div>

            {{-- Actions --}}
            <div class="mt-6 space-y-3">
                <button @click="addToCart()" :disabled="addingToCart || product?.stock_quantity <= 0"
                    class="w-full bg-indigo-600 text-white py-3 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!addingToCart">Add to cart</span>
                    <span x-show="addingToCart" style="display:none">Adding...</span>
                </button>

                <div class="grid grid-cols-2 gap-3">
                    <button @click="toggleWishlist()" :disabled="togglingWishlist"
                        class="border border-gray-300 py-2.5 rounded-lg font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                        <span x-show="!inWishlist">Save to wishlist</span>
                        <span x-show="inWishlist" style="display:none">In wishlist</span>
                    </button>
                    <button @click="showShare = true" class="border border-gray-300 py-2.5 rounded-lg font-medium text-gray-700 hover:bg-gray-50">
                        Share
                    </button>
                </div>

                <button @click="messageSeller()" :disabled="messagingSeller"
                    x-show="product?.user_id && (!window.auth.user() || product.user_id !== window.auth.user().id)"
                    class="w-full mt-3 border border-indigo-300 text-indigo-700 py-2.5 rounded-lg font-medium hover:bg-indigo-50 disabled:opacity-50"
                    style="display:none">
                    <span x-show="!messagingSeller">💬 Message seller</span>
                    <span x-show="messagingSeller" style="display:none">Opening chat...</span>
                </button>

                <button @click="findSimilar()" class="w-full mt-3 text-sm text-indigo-600 hover:text-indigo-700 underline">
                    Find visually similar items
                </button>
            </div>

            {{-- Description --}}
            <div class="mt-8">
                <h2 class="font-semibold text-gray-900 mb-2">Description</h2>
                <p class="text-gray-700 whitespace-pre-line" x-text="product?.description"></p>
            </div>
        </div>
    </div>

    {{-- Similar products modal --}}
    <div x-show="showSimilar" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showSimilar = false" style="display:none">
        <div class="bg-white rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Visually similar products</h2>
                <button @click="showSimilar = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="overflow-y-auto p-4">
                <div x-show="similarLoading" class="text-center py-10 text-gray-500">Searching...</div>
                <div x-show="!similarLoading && similarProducts.length === 0" class="text-center py-10 text-gray-500" style="display:none">
                    No similar products found.
                </div>
                <div x-show="!similarLoading && similarProducts.length > 0" class="grid sm:grid-cols-2 md:grid-cols-3 gap-4" style="display:none">
                    <template x-for="p in similarProducts" :key="p.id">
                        <a :href="'/products/' + p.id" class="block group">
                            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                <img :src="similarPrimaryImage(p)"
                                    onerror="this.src='https://placehold.co/300x300/e5e7eb/9ca3af?text=No+Image'"
                                    class="w-full h-full object-cover group-hover:scale-105 transition">
                            </div>
                            <p class="text-sm font-medium text-gray-900 mt-2 line-clamp-2" x-text="p.title"></p>
                            <p class="text-sm font-bold text-indigo-600" x-text="formatPrice(p.price_amount, p.currency)"></p>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Share modal --}}
    <div x-show="showShare && product" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showShare = false" style="display:none">
        <div class="bg-white rounded-xl max-w-sm w-full p-6">
            <h2 class="text-lg font-semibold mb-4">Share product</h2>

            <div class="space-y-2">
                <button @click="shareToPlatform('facebook')" :disabled="sharingPlatform"
                    class="w-full flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50">
                    <div class="w-9 h-9 bg-[#1877F2] rounded-full flex items-center justify-center text-white font-bold">f</div>
                    <span class="text-sm font-medium">Share on Facebook</span>
                </button>
                <button @click="copyShareLink()"
                    class="w-full flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="w-9 h-9 bg-gray-100 rounded-full flex items-center justify-center text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 015.656 0 4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.1-1.1"/></svg>
                    </div>
                    <span class="text-sm font-medium">Copy link</span>
                </button>
            </div>

            <button @click="showShare = false" class="w-full mt-4 py-2 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function productDetail() {
        return {
            productId: {{ $id ?? 'null' }},
            product: null,
            activeImage: '',
            loading: true,
            error: '',
            addingToCart: false,
            togglingWishlist: false,
            inWishlist: false,
            showShare: false,
            sharingPlatform: false,
            shareUrl: '',
            messagingSeller: false,
            showSimilar: false,
            similarLoading: false,
            similarProducts: [],
            async init() {
                const segments = window.location.pathname.split('/').filter(Boolean);
                this.productId = parseInt(segments[segments.length - 1]);
                await this.fetch();
                await this.checkWishlist();
            },
            async checkWishlist() {
                if (!window.auth.isLoggedIn() || !this.product) return;
                try {
                    const { data } = await window.api.get('/wishlist', { params: { per_page: 100 } });
                    const list = Array.isArray(data?.products) ? data.products : [];
                    this.inWishlist = list.some(p => p?.id === this.product.id);
                } catch {}
            },
            async messageSeller() {
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                if (!this.product?.user_id) return;
                this.messagingSeller = true;
                try {
                    const { data } = await window.api.post('/conversations', {
                        recipient_user_id: this.product.user_id,
                        product_id: this.product.id,
                    });
                    const convId = data?.conversation?.id;
                    if (convId) {
                        window.location.href = '/messages/' + convId;
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.messagingSeller = false;
                }
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/products/' + this.productId);
                    this.product = data.product;
                    const imgs = this.product.images || [];
                    this.activeImage = imgs.length > 0 ? (imgs.find(i => i.is_primary) || imgs[0]).url
                        : 'https://placehold.co/600x600/e5e7eb/9ca3af?text=No+Image';
                } catch (e) {
                    this.error = window.extractApiError(e) || 'Product not found.';
                } finally {
                    this.loading = false;
                }
            },
            async addToCart() {
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                this.addingToCart = true;
                try {
                    await window.api.post('/cart/items', { product_id: this.product.id, quantity: 1 });
                    await Alpine.store('cart').refresh();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Added to cart!' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.addingToCart = false;
                }
            },
            async toggleWishlist() {
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                this.togglingWishlist = true;
                try {
                    if (this.inWishlist) {
                        await window.api.delete('/wishlist/' + this.product.id);
                        this.inWishlist = false;
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Removed from wishlist.' } }));
                    } else {
                        await window.api.post('/wishlist', { product_id: this.product.id });
                        this.inWishlist = true;
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Saved to wishlist!' } }));
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.togglingWishlist = false;
                }
            },
            async ensureShareUrl() {
                if (this.shareUrl) return this.shareUrl;
                try {
                    const { data } = await window.api.get('/products/' + this.product.id + '/share');
                    this.shareUrl = data.share.share_url;
                } catch {
                    this.shareUrl = window.location.origin + '/products/' + this.product.id;
                }
                return this.shareUrl;
            },
            async shareToPlatform(platform) {
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                this.sharingPlatform = true;
                try {
                    const url = await this.ensureShareUrl();
                    await window.api.post('/products/share', {
                        product_id: this.product.id,
                        platform,
                        destination: url,
                    });
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: `Shared on ${platform}!` } }));
                    // Also open intent URL for facebook
                    if (platform === 'facebook') {
                        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank', 'width=600,height=600');
                    }
                    this.showShare = false;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.sharingPlatform = false;
                }
            },
            similarPrimaryImage(p) {
                if (p.images && p.images.length > 0) {
                    const primary = p.images.find(i => i.is_primary) || p.images[0];
                    return primary.url;
                }
                return 'https://placehold.co/300x300/e5e7eb/9ca3af?text=No+Image';
            },
            async findSimilar() {
                if (!this.product) return;
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                this.showSimilar = true;
                if (this.similarProducts.length > 0) return;
                this.similarLoading = true;
                try {
                    const { data } = await window.api.post('/ai/similarity-search', {
                        product_id: this.product.id,
                        top_k: 9,
                    });
                    this.similarProducts = (data.products || []).filter(p => p.id !== this.product.id);
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.showSimilar = false;
                } finally {
                    this.similarLoading = false;
                }
            },
            async copyShareLink() {
                const url = await this.ensureShareUrl();
                try {
                    await navigator.clipboard.writeText(url);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Link copied!' } }));
                    this.showShare = false;
                } catch {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Could not copy. Use the URL bar.' } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
