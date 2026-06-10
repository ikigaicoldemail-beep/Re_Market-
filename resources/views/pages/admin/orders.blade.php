@extends('layouts.admin')

@section('title', 'Admin · Orders')
@section('page-title', 'Orders')

@section('content')
<div x-data="adminOrders()" x-init="fetch">
    <p class="text-sm text-gray-500 mb-6">Track and update order &amp; payment status.</p>

    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4 grid sm:grid-cols-2 gap-3">
        <select x-model="filters.status" @change="apply()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
            <option value="refunded">Refunded</option>
        </select>
        <select x-model="filters.payment_status" @change="apply()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All payments</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
        </select>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading orders...</div>

    <div x-show="!loading" class="bg-white rounded-xl border border-gray-200 overflow-x-auto" style="display:none">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buyer</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="order in orders" :key="order.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">
                            <a :href="'/orders/' + order.id" target="_blank" class="font-medium text-gray-900 hover:text-indigo-600">
                                #<span x-text="order.id"></span>
                            </a>
                            <p class="text-xs text-gray-500" x-text="order.order_number || ''"></p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700" x-text="order.buyer?.name || ('#' + order.buyer_id)"></td>
                        <td class="px-4 py-3 text-sm font-medium" x-text="formatPrice(order.total_amount, order.currency)"></td>
                        <td class="px-4 py-3">
                            <select :value="order.status" @change="updateStatus(order, $event.target.value)"
                                :class="statusClass(order.status)"
                                class="text-xs rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 border-0">
                                <option value="pending">pending</option>
                                <option value="processing">processing</option>
                                <option value="completed">completed</option>
                                <option value="cancelled">cancelled</option>
                                <option value="refunded">refunded</option>
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <select :value="order.payment_status" @change="updatePayment(order, $event.target.value)"
                                :class="paymentClass(order.payment_status)"
                                class="text-xs rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 border-0">
                                <option value="pending">pending</option>
                                <option value="paid">paid</option>
                                <option value="failed">failed</option>
                                <option value="refunded">refunded</option>
                            </select>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500" x-text="formatDate(order.placed_at || order.created_at)"></td>
                    </tr>
                </template>
                <tr x-show="orders.length === 0">
                    <td colspan="6" class="text-center py-10 text-sm text-gray-500">No orders found.</td>
                </tr>
            </tbody>
        </table>

        <div x-show="meta.last_page > 1" class="flex items-center justify-center gap-2 py-4 border-t border-gray-200" style="display:none">
            <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">Previous</button>
            <span class="text-sm text-gray-600">Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span></span>
            <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">Next</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function adminOrders() {
        return {
            orders: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            page: 1,
            filters: { status: '', payment_status: '' },
            loading: true,
            statusClass(status) {
                const map = {
                    pending: 'bg-yellow-100 text-yellow-800',
                    processing: 'bg-blue-100 text-blue-800',
                    completed: 'bg-green-100 text-green-800',
                    cancelled: 'bg-gray-100 text-gray-700',
                    refunded: 'bg-purple-100 text-purple-800',
                };
                return map[status] || 'bg-gray-100 text-gray-700';
            },
            paymentClass(status) {
                const map = {
                    pending: 'bg-yellow-100 text-yellow-800',
                    paid: 'bg-green-100 text-green-800',
                    failed: 'bg-red-100 text-red-800',
                    refunded: 'bg-purple-100 text-purple-800',
                };
                return map[status] || 'bg-gray-100 text-gray-700';
            },
            formatDate(ts) {
                return ts ? new Date(ts).toLocaleString() : '';
            },
            async fetch() {
                this.loading = true;
                try {
                    const params = { page: this.page };
                    if (this.filters.status) params.status = this.filters.status;
                    if (this.filters.payment_status) params.payment_status = this.filters.payment_status;
                    const { data } = await window.api.get('/admin/orders', { params });
                    this.orders = data.orders || [];
                    this.meta = data.meta || this.meta;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            apply() { this.page = 1; this.fetch(); },
            goToPage(page) { this.page = page; this.fetch(); },
            async updateStatus(order, status) {
                try {
                    await window.api.patch('/admin/orders/' + order.id, { status });
                    order.status = status;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Order updated.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.fetch();
                }
            },
            async updatePayment(order, payment_status) {
                try {
                    await window.api.patch('/admin/orders/' + order.id, { payment_status });
                    order.payment_status = payment_status;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Payment status updated.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.fetch();
                }
            },
        };
    }
</script>
@endpush
@endsection
