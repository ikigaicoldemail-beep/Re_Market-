
@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="checkoutPage()" x-init="init">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Checkout</h1>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading...</div>

    <div x-show="!loading" class="grid lg:grid-cols-3 gap-6" style="display:none">
        <div class="lg:col-span-2 space-y-6">
            {{-- Address Selection --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Shipping Address</h2>
                    <button @click="showAddressForm = !showAddressForm" class="text-sm text-indigo-600 hover:text-indigo-700">
                        <span x-show="!showAddressForm">+ Add new</span>
                        <span x-show="showAddressForm" style="display:none">Cancel</span>
                    </button>
                </div>

                <div x-show="!addresses.length && !showAddressForm" class="text-sm text-gray-500" style="display:none">
                    No saved addresses. Add one to continue.
                </div>

                <div x-show="addresses.length > 0" class="space-y-2 mb-4" style="display:none">
                    <template x-for="addr in addresses" :key="addr.id">
                        <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                            :class="selectedAddressId === addr.id ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300'">
                            <input type="radio" :value="addr.id" x-model.number="selectedAddressId" class="mt-1">
                            <div class="flex-1 text-sm">
                                <p class="font-medium" x-text="addr.recipient_name + (addr.label ? ' (' + addr.label + ')' : '')"></p>
                                <p class="text-gray-600" x-text="addr.address_line_1"></p>
                                <p class="text-gray-600" x-text="[addr.city, addr.state, addr.country_code].filter(Boolean).join(', ')"></p>
                                <p class="text-gray-600" x-text="addr.phone"></p>
                            </div>
                        </label>
                    </template>
                </div>

                {{-- Add New Address Form --}}
                <div x-show="showAddressForm" class="border-t pt-4 space-y-3" style="display:none">
                    <div class="grid md:grid-cols-2 gap-3">
                        <input type="text" x-model="newAddress.recipient_name" placeholder="Recipient name *"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <input type="tel" x-model="newAddress.phone" placeholder="Phone *"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <input type="text" x-model="newAddress.address_line_1" placeholder="Street address *"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <div class="grid md:grid-cols-3 gap-3">
                        <input type="text" x-model="newAddress.city" placeholder="City *"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <input type="text" x-model="newAddress.state" placeholder="State *"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <input type="text" x-model="newAddress.country_code" placeholder="Country (e.g. US) *" maxlength="2"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase">
                    </div>
                    <input type="text" x-model="newAddress.postal_code" placeholder="Postal code"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <button @click="saveAddress()" :disabled="savingAddress"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                        <span x-show="!savingAddress">Save address</span>
                        <span x-show="savingAddress" style="display:none">Saving...</span>
                    </button>
                </div>
            </div>

            {{-- Order Notes --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="font-semibold text-gray-900 mb-3">Order Notes (optional)</h2>
                <textarea x-model="notes" rows="3" placeholder="Any special instructions..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
        </div>

        {{-- Order Summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-20">
                <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>

                <div class="space-y-2 mb-4 max-h-48 overflow-y-auto">
                    <template x-for="item in cart?.items || []" :key="item.id">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 truncate pr-2">
                                <span x-text="item.product.title"></span>
                                <span x-show="item.variant_label" class="text-indigo-600 ml-1" x-text="'(' + item.variant_label + ')'" style="display:none"></span>
                                <span class="text-gray-400">×<span x-text="item.quantity"></span></span>
                            </span>
                            <span class="shrink-0" x-text="formatPrice(item.line_total_amount, cart.currency)"></span>
                        </div>
                    </template>
                </div>

                <hr class="my-3 border-gray-200">

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span x-text="formatPrice(cart?.subtotal_amount, cart?.currency)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shipping</span>
                        <span x-text="formatPrice(cart?.shipping_amount, cart?.currency)"></span>
                    </div>
                    <hr class="my-2 border-gray-200">
                    <div class="flex justify-between text-base font-semibold">
                        <span>Total</span>
                        <span x-text="formatPrice(cart?.total_amount, cart?.currency)"></span>
                    </div>
                </div>

                <button @click="placeOrder()" :disabled="placingOrder || !selectedAddressId || !cart?.items?.length"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed mt-5">
                    <span x-show="!placingOrder">Place Order</span>
                    <span x-show="placingOrder" style="display:none">Placing order...</span>
                </button>

                <p x-show="!selectedAddressId" class="text-xs text-gray-500 text-center mt-2" style="display:none">
                    Select a shipping address to continue.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function checkoutPage() {
        return {
            loading: true,
            cart: null,
            addresses: [],
            selectedAddressId: null,
            showAddressForm: false,
            savingAddress: false,
            placingOrder: false,
            notes: '',
            newAddress: {
                recipient_name: '', phone: '', address_line_1: '',
                city: '', state: '', country_code: '', postal_code: '',
            },
            async init() {
                await Promise.all([this.fetchCart(), this.fetchAddresses()]);
                this.loading = false;
            },
            async fetchCart() {
                try {
                    const { data } = await window.api.get('/cart');
                    this.cart = data.cart;
                    if (!this.cart?.items?.length) {
                        window.location.href = '/cart';
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            async fetchAddresses() {
                try {
                    const { data } = await window.api.get('/addresses');
                    this.addresses = data.addresses || [];
                    const def = this.addresses.find(a => a.is_default) || this.addresses[0];
                    if (def) this.selectedAddressId = def.id;
                    if (!this.addresses.length) this.showAddressForm = true;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            async saveAddress() {
                this.savingAddress = true;
                try {
                    const payload = { ...this.newAddress };
                    payload.country_code = (payload.country_code || '').toUpperCase();
                    const { data } = await window.api.post('/addresses', payload);
                    this.addresses.push(data.address);
                    this.selectedAddressId = data.address.id;
                    this.showAddressForm = false;
                    this.newAddress = { recipient_name: '', phone: '', address_line_1: '', city: '', state: '', country_code: '', postal_code: '' };
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Address saved.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.savingAddress = false;
                }
            },
            async placeOrder() {
                if (!this.selectedAddressId) return;
                this.placingOrder = true;
                try {
                    const { data } = await window.api.post('/checkout', {
                        address_id: this.selectedAddressId,
                        notes: this.notes || null,
                    });
                    await Alpine.store('cart').refresh();
                    window.location.href = '/orders/' + data.order.id;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.placingOrder = false;
                }
            },
        };
    }
</script>
@endpush
@endsection
