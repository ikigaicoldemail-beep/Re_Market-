@extends('layouts.admin')

@section('title', 'Admin · Brands')
@section('page-title', 'Brands')

@section('content')
<div x-data="adminBrands()" x-init="fetch">
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Brand catalog shown on the home page and product pages.</p>
        <button @click="openCreate()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + New Brand
        </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4 grid sm:grid-cols-2 gap-3">
        <input type="text" x-model="filters.search" @keydown.enter="fetch()" placeholder="Search name or slug..."
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select x-model="filters.status" @change="fetch()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading brands...</div>

    <div x-show="!loading" class="bg-white rounded-xl border border-gray-200 overflow-x-auto" style="display:none">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="b in brands" :key="b.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <template x-if="b.logo_url">
                                <img :src="b.logo_url" :alt="b.name" class="w-12 h-12 rounded-lg object-contain bg-gray-50 border border-gray-200">
                            </template>
                            <template x-if="!b.logo_url">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-indigo-50 to-pink-50 flex items-center justify-center text-indigo-400 font-semibold border border-gray-200" x-text="b.name.charAt(0)"></div>
                            </template>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900" x-text="b.name"></p>
                            <p class="text-xs text-gray-500 line-clamp-1" x-text="b.description || ''"></p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 font-mono" x-text="b.slug"></td>
                        <td class="px-4 py-3 text-sm text-gray-600" x-text="b.products_count ?? 0"></td>
                        <td class="px-4 py-3">
                            <select :value="b.status" @change="updateStatus(b, $event.target.value)"
                                class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="active">active</option>
                                <option value="inactive">inactive</option>
                            </select>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600" x-text="b.sort_order"></td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button @click="openEdit(b)" class="text-sm text-indigo-600 hover:text-indigo-700">Edit</button>
                            <button @click="remove(b)" class="text-sm text-red-600 hover:text-red-700">Delete</button>
                        </td>
                    </tr>
                </template>
                <tr x-show="brands.length === 0">
                    <td colspan="7" class="text-center py-10 text-sm text-gray-500">No brands found.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div x-show="modalOpen" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         @click.self="closeModal()" style="display:none">
        <div class="bg-white rounded-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-semibold mb-4">
                <span x-text="form.id ? 'Edit brand' : 'New brand'"></span>
            </h2>
            <form @submit.prevent="submit()" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" x-model="form.name" required maxlength="255"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="form.description" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select x-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort order</label>
                        <input type="number" x-model.number="form.sort_order"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                    <div class="flex items-center gap-3">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" class="w-24 h-24 rounded-xl object-contain bg-gray-50 border border-gray-200">
                        </template>
                        <template x-if="!logoPreview">
                            <div class="w-24 h-24 rounded-xl bg-gradient-to-br from-indigo-50 to-pink-50 flex items-center justify-center text-gray-400 text-xs border border-dashed border-gray-300">No logo</div>
                        </template>
                        <div class="flex-1 space-y-2">
                            <label class="cursor-pointer inline-flex items-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-2 rounded-lg text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span x-text="logoFile ? logoFile.name : (form.id && form.logo_url ? 'Replace logo' : 'Choose logo')"></span>
                                <input type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" @change="onLogoChange($event)" class="hidden">
                            </label>
                            <p class="text-xs text-gray-500">PNG, JPG, WEBP or SVG · max 2 MB</p>
                            <button type="button" x-show="form.id && (form.logo_url || logoPreview) && !logoFile" @click="removeLogo()"
                                class="text-xs text-red-600 hover:text-red-700" style="display:none">Remove current logo</button>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 pt-3">
                    <button type="submit" :disabled="saving"
                        class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                        <span x-show="!saving" x-text="form.id ? 'Save changes' : 'Create brand'"></span>
                        <span x-show="saving" style="display:none">Saving...</span>
                    </button>
                    <button type="button" @click="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function adminBrands() {
        return {
            brands: [],
            filters: { search: '', status: '' },
            loading: true,
            modalOpen: false,
            saving: false,
            form: { id: null, name: '', description: '', status: 'active', sort_order: 0, logo_url: null },
            logoFile: null,
            logoPreview: null,
            removeLogoFlag: false,

            async fetch() {
                this.loading = true;
                try {
                    const params = {};
                    if (this.filters.search) params.search = this.filters.search;
                    if (this.filters.status) params.status = this.filters.status;
                    const { data } = await window.api.get('/admin/brands', { params });
                    this.brands = data.brands || [];
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            openCreate() {
                this.form = { id: null, name: '', description: '', status: 'active', sort_order: 0, logo_url: null };
                this.logoFile = null; this.logoPreview = null; this.removeLogoFlag = false;
                this.modalOpen = true;
            },
            openEdit(b) {
                this.form = { id: b.id, name: b.name, description: b.description || '', status: b.status, sort_order: b.sort_order || 0, logo_url: b.logo_url || null };
                this.logoFile = null; this.logoPreview = b.logo_url || null; this.removeLogoFlag = false;
                this.modalOpen = true;
            },
            closeModal() { this.modalOpen = false; },
            onLogoChange(e) {
                const file = e.target.files?.[0];
                if (!file) return;
                if (file.size > 2 * 1024 * 1024) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Logo must be 2 MB or smaller.' } }));
                    e.target.value = '';
                    return;
                }
                this.logoFile = file;
                this.logoPreview = URL.createObjectURL(file);
                this.removeLogoFlag = false;
            },
            removeLogo() { this.logoFile = null; this.logoPreview = null; this.removeLogoFlag = true; },
            async submit() {
                this.saving = true;
                try {
                    const fd = new FormData();
                    fd.append('name', this.form.name);
                    if (this.form.description) fd.append('description', this.form.description);
                    fd.append('status', this.form.status);
                    fd.append('sort_order', this.form.sort_order ?? 0);
                    if (this.logoFile) fd.append('logo', this.logoFile);
                    if (this.removeLogoFlag) fd.append('remove_logo', 1);

                    const url = this.form.id ? '/admin/brands/' + this.form.id : '/admin/brands';
                    const { data } = await window.api.post(url, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message || 'Saved.' } }));
                    this.closeModal();
                    await this.fetch();
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.saving = false;
                }
            },
            async updateStatus(b, status) {
                try {
                    const fd = new FormData();
                    fd.append('status', status);
                    await window.api.post('/admin/brands/' + b.id, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                    b.status = status;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Status updated.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.fetch();
                }
            },
            async remove(b) {
                if (!confirm('Delete brand "' + b.name + '"? Products in this brand will be unlinked.')) return;
                try {
                    await window.api.delete('/admin/brands/' + b.id);
                    this.brands = this.brands.filter(x => x.id !== b.id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Brand deleted.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
