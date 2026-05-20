@extends('layouts.admin')

@section('title', 'Admin · Categories')
@section('page-title', 'Categories')

@section('content')
<div x-data="adminCategories()" x-init="fetch">
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Manage marketplace categories &amp; their logos.</p>
        <button @click="openCreate()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + New Category
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

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading categories...</div>

    <div x-show="!loading" class="bg-white rounded-xl border border-gray-200 overflow-x-auto" style="display:none">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="cat in displayCategories" :key="cat.id">
                    <tr class="hover:bg-gray-50" :class="cat.parent_id ? 'bg-gray-50/40' : ''">
                        <td class="px-4 py-3">
                            <div class="flex items-center" :style="cat.parent_id ? 'padding-left: 24px' : ''">
                                <template x-if="cat.parent_id">
                                    <span class="text-gray-300 mr-2">↳</span>
                                </template>
                                <template x-if="cat.logo_url">
                                    <img :src="cat.logo_url" :alt="cat.name"
                                        :class="cat.parent_id ? 'w-10 h-10' : 'w-12 h-12'"
                                        class="rounded-lg object-cover bg-gray-100 border border-gray-200">
                                </template>
                                <template x-if="!cat.logo_url">
                                    <div :class="cat.parent_id ? 'w-10 h-10' : 'w-12 h-12'"
                                         class="rounded-lg bg-gradient-to-br from-indigo-50 to-pink-50 flex items-center justify-center text-indigo-400 font-semibold border border-gray-200"
                                         x-text="cat.name.charAt(0).toUpperCase()"></div>
                                </template>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 flex items-center gap-2">
                                <span x-text="cat.name"></span>
                                <span x-show="!cat.parent_id && childCountFor(cat.id) > 0"
                                    class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700"
                                    x-text="childCountFor(cat.id) + ' subs'" style="display:none"></span>
                            </p>
                            <p class="text-xs text-gray-500 line-clamp-1" x-text="cat.description || ''"></p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 font-mono" x-text="cat.slug"></td>
                        <td class="px-4 py-3 text-sm">
                            <template x-if="!cat.parent_id">
                                <span class="inline-block text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-medium">Main</span>
                            </template>
                            <template x-if="cat.parent_id">
                                <span class="inline-block text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                    Sub of <span class="font-medium" x-text="parentName(cat.parent_id)"></span>
                                </span>
                            </template>
                        </td>
                        <td class="px-4 py-3">
                            <select :value="cat.status" @change="updateStatus(cat, $event.target.value)"
                                class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="active">active</option>
                                <option value="inactive">inactive</option>
                            </select>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600" x-text="cat.sort_order"></td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button @click="openEdit(cat)" class="text-sm text-indigo-600 hover:text-indigo-700">Edit</button>
                            <button @click="remove(cat)" class="text-sm text-red-600 hover:text-red-700">Delete</button>
                        </td>
                    </tr>
                </template>
                <tr x-show="categories.length === 0">
                    <td colspan="7" class="text-center py-10 text-sm text-gray-500">No categories found.</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Create / Edit modal --}}
    <div x-show="modalOpen" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         @click.self="closeModal()" style="display:none">
        <div class="bg-white rounded-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-semibold mb-4">
                <span x-text="form.id ? 'Edit category' : 'New category'"></span>
            </h2>
            <form @submit.prevent="submit()" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" x-model="form.name" required maxlength="255"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent (main category)</label>
                    <select x-model="form.parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">— Top level (main category) —</option>
                        <template x-for="opt in categories.filter(c => !c.parent_id && c.id !== form.id)" :key="opt.id">
                            <option :value="opt.id" x-text="opt.name"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Leave blank to make this a main category. Otherwise this becomes a subcategory under the chosen parent.</p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category image</label>
                    <div class="flex items-center gap-3">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" class="w-24 h-24 rounded-xl object-cover bg-gray-100 border border-gray-200">
                        </template>
                        <template x-if="!logoPreview">
                            <div class="w-24 h-24 rounded-xl bg-gradient-to-br from-indigo-50 to-pink-50 flex items-center justify-center text-gray-400 text-xs border border-dashed border-gray-300">No image</div>
                        </template>
                        <div class="flex-1 space-y-2">
                            <label class="cursor-pointer inline-flex items-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-2 rounded-lg text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span x-text="logoFile ? logoFile.name : (form.id && form.logo_url ? 'Replace image' : 'Choose image')"></span>
                                <input type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" @change="onLogoChange($event)" class="hidden">
                            </label>
                            <p class="text-xs text-gray-500">PNG, JPG, WEBP or SVG · max 2 MB · shown to buyers when browsing categories</p>
                            <button type="button" x-show="form.id && (form.logo_url || logoPreview) && !logoFile" @click="removeLogo()"
                                class="text-xs text-red-600 hover:text-red-700" style="display:none">Remove current image</button>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 pt-3">
                    <button type="submit" :disabled="saving"
                        class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                        <span x-show="!saving" x-text="form.id ? 'Save changes' : 'Create category'"></span>
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
    function adminCategories() {
        return {
            categories: [],
            filters: { search: '', status: '' },
            loading: true,
            modalOpen: false,
            saving: false,
            form: { id: null, name: '', parent_id: '', description: '', status: 'active', sort_order: 0, logo_url: null },
            logoFile: null,
            logoPreview: null,
            removeLogoFlag: false,

            async fetch() {
                this.loading = true;
                try {
                    const params = {};
                    if (this.filters.search) params.search = this.filters.search;
                    if (this.filters.status) params.status = this.filters.status;
                    const { data } = await window.api.get('/admin/categories', { params });
                    this.categories = data.categories || [];
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },

            get displayCategories() {
                // Render parents followed by their children so the table reads as a tree.
                const parents = this.categories.filter(c => !c.parent_id)
                    .sort((a, b) => (a.sort_order - b.sort_order) || a.name.localeCompare(b.name));
                const orphans = this.categories.filter(c => c.parent_id && !this.categories.some(p => p.id === c.parent_id));
                const out = [];
                for (const p of parents) {
                    out.push(p);
                    const kids = this.categories
                        .filter(c => c.parent_id === p.id)
                        .sort((a, b) => (a.sort_order - b.sort_order) || a.name.localeCompare(b.name));
                    out.push(...kids);
                }
                out.push(...orphans);
                return out;
            },

            childCountFor(parentId) {
                return this.categories.filter(c => c.parent_id === parentId).length;
            },

            parentName(parentId) {
                if (!parentId) return null;
                const p = this.categories.find(c => c.id === parentId);
                return p ? p.name : '#' + parentId;
            },

            openCreate() {
                this.form = { id: null, name: '', parent_id: '', description: '', status: 'active', sort_order: 0, logo_url: null };
                this.logoFile = null;
                this.logoPreview = null;
                this.removeLogoFlag = false;
                this.modalOpen = true;
            },

            openEdit(cat) {
                this.form = {
                    id: cat.id,
                    name: cat.name || '',
                    parent_id: cat.parent_id || '',
                    description: cat.description || '',
                    status: cat.status || 'active',
                    sort_order: cat.sort_order || 0,
                    logo_url: cat.logo_url || null,
                };
                this.logoFile = null;
                this.logoPreview = cat.logo_url || null;
                this.removeLogoFlag = false;
                this.modalOpen = true;
            },

            closeModal() {
                this.modalOpen = false;
            },

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

            removeLogo() {
                this.logoFile = null;
                this.logoPreview = null;
                this.removeLogoFlag = true;
            },

            async submit() {
                this.saving = true;
                try {
                    const fd = new FormData();
                    fd.append('name', this.form.name);
                    if (this.form.parent_id) fd.append('parent_id', this.form.parent_id);
                    if (this.form.description) fd.append('description', this.form.description);
                    fd.append('status', this.form.status);
                    fd.append('sort_order', this.form.sort_order ?? 0);
                    if (this.logoFile) fd.append('logo', this.logoFile);
                    if (this.removeLogoFlag) fd.append('remove_logo', 1);

                    const url = this.form.id
                        ? '/admin/categories/' + this.form.id
                        : '/admin/categories';
                    const { data } = await window.api.post(url, fd, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    });

                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message || 'Saved.' } }));
                    this.closeModal();
                    await this.fetch();
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.saving = false;
                }
            },

            async updateStatus(cat, status) {
                try {
                    const fd = new FormData();
                    fd.append('status', status);
                    await window.api.post('/admin/categories/' + cat.id, fd, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    });
                    cat.status = status;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Status updated.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.fetch();
                }
            },

            async remove(cat) {
                if (!confirm('Delete category "' + cat.name + '"? Products in this category will be moved to "Other".')) return;
                try {
                    await window.api.delete('/admin/categories/' + cat.id);
                    this.categories = this.categories.filter(c => c.id !== cat.id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Category deleted.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
