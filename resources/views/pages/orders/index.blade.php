@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="ordersPage()" x-init="fetch">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6" data-i18n="orders.title">My Orders</h1>

    <div x-show="loading" class="text-center py-20 text-gray-500" data-i18n="orders.loading">Loading orders...</div>

    <div x-show="!loading && orders.length === 0" class="text-center py-20 bg-white rounded-xl border border-gray-200" style="display:none">
        <p class="text-gray-500 mb-4" data-i18n="orders.empty">You haven't placed any orders yet.</p>
        <a href="/" class="inline-block bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-indigo-700" data-i18n="orders.browse_products">Browse products</a>
    </div>

    <div x-show="!loading && orders.length > 0" class="space-y-3" style="display:none">
        <template x-for="order in orders" :key="order.id">
            <a :href="'/orders/' + order.id"
                class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-300 hover:shadow-sm transition">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-500" x-text="'Order #' + order.order_number"></p>
                        <p class="text-sm text-gray-600 mt-1" x-text="formatDate(order.placed_at || order.created_at)"></p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span :class="statusClass(order.status)" class="text-xs px-2 py-1 rounded-full font-medium" x-text="order.status"></span>
                        <span :class="paymentClass(order.payment_status)" class="text-xs px-2 py-1 rounded-full font-medium" x-text="'Payment: ' + order.payment_status"></span>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-sm text-gray-600">
                        <span x-text="(order.items?.length || 0)"></span> item(s)
                    </span>
                    <span class="font-semibold text-gray-900" x-text="formatPrice(order.total_amount, order.currency)"></span>
                </div>
            </a>
        </template>

        {{-- Pagination --}}
        <div x-show="meta.last_page > 1" class="flex items-center justify-center gap-2 mt-6" style="display:none">
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
    function ordersPage() {
        return {
            orders: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            page: 1,
            loading: true,
            formatDate(date) {
                if (!date) return '';
                return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
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
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/orders', { params: { page: this.page } });
                    this.orders = data.orders || [];
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
