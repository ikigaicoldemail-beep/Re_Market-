@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="orderDetail()" x-init="init">
    <a href="/orders" class="text-sm text-indigo-600 hover:text-indigo-700 mb-4 inline-block">← Back to my orders</a>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading order...</div>

    <div x-show="!loading && order" style="display:none">
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900" x-text="'Order #' + order.order_number"></h1>
                    <p class="text-sm text-gray-500 mt-1" x-text="formatDate(order.placed_at || order.created_at)"></p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span :class="statusClass(order.status)" class="text-xs px-2.5 py-1 rounded-full font-medium" x-text="order.status"></span>
                    <span :class="paymentClass(order.payment_status)" class="text-xs px-2.5 py-1 rounded-full font-medium" x-text="'Payment: ' + order.payment_status"></span>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6 pt-4 border-t border-gray-200">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Shipping to</h3>
                    <div class="text-sm text-gray-600" x-show="order.address" style="display:none">
                        <p class="font-medium text-gray-900" x-text="order.address?.recipient_name"></p>
                        <p x-text="order.address?.address_line_1"></p>
                        <p x-text="[order.address?.city, order.address?.state, order.address?.country_code].filter(Boolean).join(', ')"></p>
                        <p x-text="order.address?.phone"></p>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">From</h3>
                    <p class="text-sm text-gray-900 font-medium" x-text="order.store?.name || '—'"></p>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-4">Items</h2>
            <div class="space-y-4">
                <template x-for="item in order.items || []" :key="item.id">
                    <div class="flex gap-4 pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg shrink-0 overflow-hidden">
                            <img :src="itemImage(item)" onerror="this.src='https://placehold.co/200x200/e5e7eb/9ca3af?text=N/A'"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 line-clamp-2" x-text="item.product_title"></p>
                            <p x-show="item.variant_label" class="text-xs text-indigo-600 font-medium mt-0.5 bg-indigo-50 px-2 py-0.5 rounded inline-block" x-text="item.variant_label" style="display:none"></p>
                            <p class="text-xs text-gray-500 mt-1" x-text="item.product_condition_label || ''"></p>
                            <div class="flex items-center justify-between mt-2 text-sm">
                                <span class="text-gray-600">Qty: <span x-text="item.quantity"></span></span>
                                <span class="font-medium" x-text="formatPrice(item.line_total_amount, order.currency)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Totals --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Summary</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span x-text="formatPrice(order.subtotal_amount, order.currency)"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Shipping</span>
                    <span x-text="formatPrice(order.shipping_amount, order.currency)"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Discount</span>
                    <span x-text="'-' + formatPrice(order.discount_amount, order.currency)"></span>
                </div>
                <hr class="my-2 border-gray-200">
                <div class="flex justify-between text-base font-semibold">
                    <span>Total</span>
                    <span x-text="formatPrice(order.total_amount, order.currency)"></span>
                </div>
            </div>
            <p x-show="order.notes" class="mt-4 pt-4 border-t border-gray-200 text-sm text-gray-600" style="display:none">
                <span class="font-medium">Notes:</span> <span x-text="order.notes"></span>
            </p>
        </div>

        {{-- Cancel order --}}
        <div x-show="canCancel" class="mt-6 bg-white rounded-xl border border-gray-200 p-6" style="display:none">
            <h2 class="font-semibold text-gray-900 mb-1">Cancel order</h2>
            <p class="text-sm text-gray-500 mb-3">Cancel while the order is still pending. After payment, contact the seller for a refund.</p>
            <button @click="cancelOrder()" :disabled="cancelling"
                class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50">
                <span x-show="!cancelling">Cancel this order</span>
                <span x-show="cancelling" style="display:none">Cancelling...</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function orderDetail() {
        return {
            order: null,
            loading: true,
            cancelling: false,
            orderId: null,
            get canCancel() {
                return this.order
                    && ['pending', 'processing'].includes(this.order.status)
                    && this.order.payment_status !== 'paid';
            },
            formatDate(date) {
                if (!date) return '';
                return new Date(date).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
            },
            statusClass(status) {
                const map = {
                    pending: 'bg-yellow-100 text-yellow-800',
                    paid: 'bg-blue-100 text-blue-800',
                    completed: 'bg-green-100 text-green-800',
                    cancelled: 'bg-red-100 text-red-800',
                };
                return map[status] || 'bg-gray-100 text-gray-800';
            },
            paymentClass(status) {
                const map = {
                    paid: 'bg-green-100 text-green-800',
                    pending: 'bg-yellow-100 text-yellow-800',
                    failed: 'bg-red-100 text-red-800',
                };
                return map[status] || 'bg-gray-100 text-gray-800';
            },
            itemImage(item) {
                if (item.product_image_url) return item.product_image_url;
                if (!item.product_image_path) return 'https://placehold.co/200x200/e5e7eb/9ca3af?text=N/A';
                if (item.product_image_path.startsWith('http')) return item.product_image_path;
                return '/storage/products/' + item.product_image_path;
            },
            async init() {
                const segments = window.location.pathname.split('/').filter(Boolean);
                this.orderId = parseInt(segments[segments.length - 1]);
                await this.fetch();
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/orders/' + this.orderId);
                    this.order = data.order;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            async cancelOrder() {
                if (!confirm('Cancel this order? This cannot be undone.')) return;
                this.cancelling = true;
                try {
                    const { data } = await window.api.post('/orders/' + this.orderId + '/cancel');
                    this.order = data.order;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message || 'Order cancelled.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.cancelling = false;
                }
            },
        };
    }
</script>
@endpush
@endsection
