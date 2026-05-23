@extends('layouts.admin')

@section('title', 'Admin · Stores')
@section('page-title', 'Stores')

@section('content')
<div x-data="adminStores()" x-init="fetch">
    <p class="text-sm text-gray-500 mb-6">Approve, suspend, and verify storefronts.</p>

    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4 grid sm:grid-cols-3 gap-3">
        <input type="text" x-model="filters.search" @keydown.enter="apply()" placeholder="Search name, slug, email..."
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select x-model="filters.status" @change="apply()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All statuses</option>
            <option value="draft">Draft</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="archived">Archived</option>
        </select>
        <select x-model="filters.is_verified" @change="apply()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Verified: any</option>
            <option value="1">Verified only</option>
            <option value="0">Unverified only</option>
        </select>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading stores...</div>

    <div x-show="!loading" class="bg-white rounded-xl border border-gray-200 overflow-x-auto" style="display:none">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="store in stores" :key="store.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a :href="'/stores/' + store.id" target="_blank" class="font-medium text-gray-900 hover:text-indigo-600" x-text="store.name"></a>
                            <p class="text-xs text-gray-500" x-text="store.slug || ''"></p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700" x-text="store.user?.name || ('#' + store.user_id)"></td>
                        <td class="px-4 py-3">
                            <select :value="store.status" @change="updateStatus(store, $event.target.value)"
                                class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="draft">draft</option>
                                <option value="active">active</option>
                                <option value="suspended">suspended</option>
                                <option value="archived">archived</option>
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <button @click="toggleVerified(store)"
                                :class="store.is_verified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'"
                                class="text-xs px-2 py-1 rounded font-medium">
                                <span x-text="store.is_verified ? 'Verified' : 'Unverified'"></span>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="remove(store)" class="text-sm text-red-600 hover:text-red-700">Delete</button>
                        </td>
                    </tr>
                </template>
                <tr x-show="stores.length === 0">
                    <td colspan="5" class="text-center py-10 text-sm text-gray-500">No stores found.</td>
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
    function adminStores() {
        return {
            stores: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            page: 1,
            filters: { search: '', status: '', is_verified: '' },
            loading: true,
            async fetch() {
                this.loading = true;
                try {
                    const params = { page: this.page };
                    if (this.filters.search) params.search = this.filters.search;
                    if (this.filters.status) params.status = this.filters.status;
                    if (this.filters.is_verified !== '') params.is_verified = this.filters.is_verified;
                    const { data } = await window.api.get('/admin/stores', { params });
                    this.stores = data.stores || [];
                    this.meta = data.meta || this.meta;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            apply() { this.page = 1; this.fetch(); },
            goToPage(page) { this.page = page; this.fetch(); },
            async updateStatus(store, status) {
                try {
                    await window.api.patch('/admin/stores/' + store.id, { status });
                    store.status = status;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Status updated.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.fetch();
                }
            },
            async toggleVerified(store) {
                const next = !store.is_verified;
                try {
                    await window.api.patch('/admin/stores/' + store.id, { is_verified: next });
                    store.is_verified = next;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: next ? 'Store verified.' : 'Verification removed.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            async remove(store) {
                if (!confirm('Delete store "' + store.name + '"?')) return;
                try {
                    await window.api.delete('/admin/stores/' + store.id);
                    this.stores = this.stores.filter(s => s.id !== store.id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Store deleted.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
