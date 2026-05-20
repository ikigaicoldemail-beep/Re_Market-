@extends('layouts.app')

@section('title', 'My Products')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="myProducts()" x-init="fetch">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">My Products</h1>
            <p class="text-sm text-gray-500 mt-1">Manage everything you're selling.</p>
        </div>
        <a href="{{ route('me.products.create') }}"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + New Product
        </a>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading...</div>

    <div x-show="!loading && needsStore" class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center" style="display:none">
        <p class="text-gray-700 mb-3">You need to create a store before listing products.</p>
        <a href="{{ route('me.store') }}" class="inline-block bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            Create my store
        </a>
    </div>

    <div x-show="!loading && !needsStore && products.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center" style="display:none">
        <p class="text-gray-500 mb-4">No products yet. Let's add your first listing.</p>
        <a href="{{ route('me.products.create') }}" class="inline-block bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-indigo-700">
            Create product
        </a>
    </div>

    <div x-show="!loading && products.length > 0" class="bg-white rounded-xl border border-gray-200 overflow-hidden" style="display:none">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="product in products" :key="product.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                    <img :src="primaryImage(product)"
                                        onerror="this.src='https://placehold.co/100x100/e5e7eb/9ca3af?text=N/A'"
                                        class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0">
                                    <a :href="'/products/' + product.id" target="_blank"
                                        class="font-medium text-gray-900 hover:text-indigo-600 line-clamp-1" x-text="product.title"></a>
                                    <p class="text-xs text-gray-500" x-text="product.location_city || ''"></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm" x-text="formatPrice(product.price_amount, product.currency)"></td>
                        <td class="px-4 py-3 text-sm" x-text="product.stock_quantity ?? 0"></td>
                        <td class="px-4 py-3">
                            <span :class="statusClass(product.status)" class="text-xs px-2 py-1 rounded-full font-medium" x-text="product.status"></span>
                            <p x-show="product.status === 'scheduled' && product.schedule_at"
                                class="text-[11px] text-purple-700 mt-1"
                                x-text="'Goes live ' + formatScheduleAt(product.schedule_at)"
                                style="display:none"></p>
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a :href="'/me/products/' + product.id + '/edit'" class="text-indigo-600 hover:text-indigo-700 mr-3">Edit</a>
                            <button @click="remove(product)" class="text-red-600 hover:text-red-700">Delete</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        <div x-show="meta.last_page > 1" class="flex items-center justify-center gap-2 py-4 border-t border-gray-200" style="display:none">
            <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">Previous</button>
            <span class="text-sm text-gray-600">
                Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span>
            </span>
            <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">Next</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function myProducts() {
        return {
            products: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            page: 1,
            loading: true,
            needsStore: false,
            primaryImage(product) {
                if (product.images && product.images.length > 0) {
                    const primary = product.images.find(i => i.is_primary) || product.images[0];
                    return primary.url;
                }
                return 'https://placehold.co/100x100/e5e7eb/9ca3af?text=N/A';
            },
            statusClass(status) {
                const map = {
                    draft: 'bg-gray-100 text-gray-700',
                    pending: 'bg-yellow-100 text-yellow-800',
                    published: 'bg-green-100 text-green-800',
                    scheduled: 'bg-purple-100 text-purple-800',
                    sold: 'bg-blue-100 text-blue-800',
                    inactive: 'bg-gray-100 text-gray-700',
                    archived: 'bg-gray-100 text-gray-500',
                };
                return map[status] || 'bg-gray-100 text-gray-700';
            },
            formatScheduleAt(value) {
                if (!value) return '';
                const d = new Date(value);
                if (isNaN(d.getTime())) return '';
                return d.toLocaleString(undefined, {
                    month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit',
                });
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/me/products', { params: { page: this.page } });
                    this.products = data.products || [];
                    this.meta = data.meta || this.meta;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }

                // Check if user has store
                try {
                    await window.api.get('/me/store');
                } catch (e) {
                    if (e?.response?.status === 404) this.needsStore = true;
                }
            },
            goToPage(page) {
                this.page = page;
                this.fetch();
            },
            async remove(product) {
                if (!confirm(`Delete "${product.title}"? This cannot be undone.`)) return;
                try {
                    await window.api.delete('/products/' + product.id);
                    this.products = this.products.filter(p => p.id !== product.id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Product deleted.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
