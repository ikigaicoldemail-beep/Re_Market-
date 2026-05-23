@extends('layouts.admin')

@section('title', 'Admin · Products')
@section('page-title', 'Products')

@section('content')
<div x-data="adminProducts()" x-init="fetch">
    <p class="text-sm text-gray-500 mb-6">Review listings, moderate, and remove content.</p>

    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4 grid sm:grid-cols-3 gap-3">
        <input type="text" x-model="filters.search" @keydown.enter="apply()" placeholder="Search title, slug, SKU..."
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select x-model="filters.status" @change="apply()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All statuses</option>
            <option value="draft">Draft</option>
            <option value="pending">Pending</option>
            <option value="published">Published</option>
            <option value="sold">Sold</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
        </select>
        <select x-model="filters.moderation_status" @change="apply()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All moderation</option>
            <option value="pending">Pending review</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading products...</div>

    <div x-show="!loading" class="bg-white rounded-xl border border-gray-200 overflow-x-auto" style="display:none">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moderation</th>
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
                                <a :href="'/products/' + product.id" target="_blank" class="font-medium text-gray-900 hover:text-indigo-600 line-clamp-1" x-text="product.title"></a>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700" x-text="product.store?.name || product.user?.name || ('#' + product.user_id)"></td>
                        <td class="px-4 py-3 text-sm" x-text="formatPrice(product.price_amount, product.currency)"></td>
                        <td class="px-4 py-3">
                            <select :value="product.status" @change="updateStatus(product, $event.target.value)"
                                class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="draft">draft</option>
                                <option value="pending">pending</option>
                                <option value="published">published</option>
                                <option value="sold">sold</option>
                                <option value="inactive">inactive</option>
                                <option value="archived">archived</option>
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <select :value="product.moderation_status" @change="updateModeration(product, $event.target.value)"
                                :class="moderationClass(product.moderation_status)"
                                class="text-xs rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 border-0">
                                <option value="pending">pending</option>
                                <option value="approved">approved</option>
                                <option value="rejected">rejected</option>
                            </select>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <button @click="toggleFeatured(product)"
                                :title="product.is_featured ? 'Unfeature' : 'Mark as featured'"
                                :class="product.is_featured ? 'text-amber-500 hover:text-amber-600' : 'text-gray-400 hover:text-amber-500'">
                                <template x-if="product.is_featured">
                                    <x-heroicon-s-star class="w-5 h-5"/>
                                </template>
                                <template x-if="!product.is_featured">
                                    <x-heroicon-o-star class="w-5 h-5"/>
                                </template>
                            </button>
                            <button @click="remove(product)" class="text-sm text-red-600 hover:text-red-700">Delete</button>
                        </td>
                    </tr>
                </template>
                <tr x-show="products.length === 0">
                    <td colspan="6" class="text-center py-10 text-sm text-gray-500">No products found.</td>
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
    function adminProducts() {
        return {
            products: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            page: 1,
            filters: { search: '', status: '', moderation_status: '' },
            loading: true,
            primaryImage(product) {
                if (product.images && product.images.length > 0) {
                    const primary = product.images.find(i => i.is_primary) || product.images[0];
                    return primary.urls?.thumb_webp || primary.urls?.thumb || primary.url;
                }
                return 'https://placehold.co/100x100/e5e7eb/9ca3af?text=N/A';
            },
            moderationClass(status) {
                const map = {
                    pending: 'bg-yellow-100 text-yellow-800',
                    approved: 'bg-green-100 text-green-800',
                    rejected: 'bg-red-100 text-red-800',
                };
                return map[status] || 'bg-gray-100 text-gray-700';
            },
            async fetch() {
                this.loading = true;
                try {
                    const params = { page: this.page };
                    if (this.filters.search) params.search = this.filters.search;
                    if (this.filters.status) params.status = this.filters.status;
                    if (this.filters.moderation_status) params.moderation_status = this.filters.moderation_status;
                    const { data } = await window.api.get('/admin/products', { params });
                    this.products = data.products || [];
                    this.meta = data.meta || this.meta;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            apply() { this.page = 1; this.fetch(); },
            goToPage(page) { this.page = page; this.fetch(); },
            async updateStatus(product, status) {
                try {
                    await window.api.patch('/admin/products/' + product.id, { status });
                    product.status = status;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Status updated.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.fetch();
                }
            },
            async updateModeration(product, moderation_status) {
                try {
                    await window.api.patch('/admin/products/' + product.id, { moderation_status });
                    product.moderation_status = moderation_status;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Moderation updated.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.fetch();
                }
            },
            async toggleFeatured(product) {
                const next = !product.is_featured;
                try {
                    const { data } = await window.api.post('/admin/products/' + product.id + '/feature', { is_featured: next });
                    product.is_featured = data.product?.is_featured ?? next;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message || 'Updated.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            async remove(product) {
                if (!confirm('Delete product "' + product.title + '"?')) return;
                try {
                    await window.api.delete('/admin/products/' + product.id);
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
