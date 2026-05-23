@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="cartPage()" x-init="fetch">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6" data-i18n="cart.title">Shopping Cart</h1>

    <div x-show="loading" class="text-center py-20 text-gray-500" data-i18n="cart.loading">Loading cart...</div>

    <div x-show="!loading && (!cart?.items?.length)" class="text-center py-20 bg-white rounded-xl border border-gray-200" style="display:none">
        <p class="text-gray-500 mb-4" data-i18n="cart.empty">Your cart is empty.</p>
        <a href="/" class="inline-block bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-indigo-700" data-i18n="cart.start_shopping">Start shopping</a>
    </div>

    <div x-show="!loading && cart?.items?.length > 0" class="grid lg:grid-cols-3 gap-6" style="display:none">
        <div class="lg:col-span-2 space-y-3">
            <template x-for="item in cart.items" :key="item.id">
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex gap-4">
                    <a :href="'/products/' + item.product.id" class="shrink-0">
                        <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden">
                            <img :src="primaryImage(item.product)"
                                onerror="this.src='https://placehold.co/200x200/e5e7eb/9ca3af?text=N/A'"
                                class="w-full h-full object-cover">
                        </div>
                    </a>
                    <div class="flex-1 min-w-0">
                        <a :href="'/products/' + item.product.id" class="font-medium text-gray-900 hover:text-indigo-600 line-clamp-2"
                            x-text="item.product.title"></a>
                        <p x-show="item.variant_label" class="text-xs text-indigo-600 font-medium mt-0.5 bg-indigo-50 px-2 py-0.5 rounded inline-block" x-text="item.variant_label" style="display:none"></p>
                        <p class="text-sm text-gray-500 mt-1" x-text="formatPrice(item.unit_price_amount, cart.currency)"></p>

                        <div class="flex items-center justify-between mt-3">
                            <div class="flex items-center border border-gray-300 rounded-lg">
                                <button @click="updateQty(item, item.quantity - 1)" :disabled="item.quantity <= 1"
                                    class="px-3 py-1 text-gray-700 hover:bg-gray-50 disabled:opacity-50">−</button>
                                <span class="px-3 py-1 text-sm font-medium" x-text="item.quantity"></span>
                                <button @click="updateQty(item, item.quantity + 1)"
                                    class="px-3 py-1 text-gray-700 hover:bg-gray-50">+</button>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="font-semibold text-gray-900" x-text="formatPrice(item.line_total_amount, cart.currency)"></span>
                                <button @click="removeItem(item)" class="text-sm text-red-600 hover:text-red-700" data-i18n="cart.remove">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <button @click="clearCart()" class="text-sm text-gray-500 hover:text-red-600 mt-2" data-i18n="cart.clear_all">
                Clear entire cart
            </button>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-20">
                <h2 class="font-semibold text-gray-900 mb-4" data-i18n="cart.summary">Order Summary</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600" data-i18n="cart.subtotal">Subtotal</span>
                        <span x-text="formatPrice(cart?.subtotal_amount, cart?.currency)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600" data-i18n="cart.shipping">Shipping</span>
                        <span x-text="formatPrice(cart?.shipping_amount, cart?.currency)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600" data-i18n="cart.discount">Discount</span>
                        <span x-text="'-' + formatPrice(cart?.discount_amount, cart?.currency)"></span>
                    </div>
                    <hr class="my-3 border-gray-200">
                    <div class="flex justify-between text-base font-semibold">
                        <span data-i18n="cart.total">Total</span>
                        <span x-text="formatPrice(cart?.total_amount, cart?.currency)"></span>
                    </div>
                </div>
                <a href="/checkout"
                    class="block text-center w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 mt-5" data-i18n="cart.checkout">
                    Proceed to Checkout
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function cartPage() {
        return {
            cart: null,
            loading: true,
            primaryImage(product) {
                if (product.images && product.images.length > 0) {
                    const primary = product.images.find(i => i.is_primary) || product.images[0];
                    return primary.url;
                }
                return 'https://placehold.co/200x200/e5e7eb/9ca3af?text=N/A';
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/cart');
                    this.cart = data.cart;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            async updateQty(item, qty) {
                if (qty < 1) return;
                try {
                    const { data } = await window.api.put('/cart/items/' + item.id, { quantity: qty });
                    this.cart = data.cart;
                    await Alpine.store('cart').refresh();
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            async removeItem(item) {
                try {
                    const { data } = await window.api.delete('/cart/items/' + item.id);
                    this.cart = data.cart;
                    await Alpine.store('cart').refresh();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Item removed.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            async clearCart() {
                if (!confirm('Clear your entire cart?')) return;
                try {
                    const { data } = await window.api.delete('/cart');
                    this.cart = data.cart;
                    await Alpine.store('cart').refresh();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Cart cleared.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
