@extends('layouts.admin')

@section('title', 'Admin · Promo Banners')
@section('page-title', 'Promo Banners')

@section('content')
<div x-data="adminBanners()" x-init="fetch">
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Hero slider on the home page. Active banners show within their date window.</p>
        <button @click="openCreate()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + New Banner
        </button>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading banners...</div>

    <div x-show="!loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4" style="display:none">
        <template x-for="b in banners" :key="b.id">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="aspect-[5/2] bg-gray-100 relative">
                    <img :src="b.image_url" :alt="b.title || 'Banner'" class="w-full h-full object-cover">
                    <span class="absolute top-2 left-2 text-[10px] font-semibold px-2 py-0.5 rounded-full"
                        :class="b.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
                        x-text="b.status"></span>
                </div>
                <div class="p-3">
                    <h3 class="font-medium text-gray-900 line-clamp-1" x-text="b.title || '(untitled)'"></h3>
                    <p class="text-xs text-gray-500 line-clamp-1" x-text="b.subtitle || ''"></p>
                    <p class="text-xs text-indigo-600 mt-1 truncate" x-show="b.link_url" x-text="b.link_url" style="display:none"></p>
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-xs text-gray-500">sort: <span x-text="b.sort_order"></span></span>
                        <div class="flex gap-2">
                            <button @click="openEdit(b)" class="text-sm text-indigo-600 hover:text-indigo-700">Edit</button>
                            <button @click="remove(b)" class="text-sm text-red-600 hover:text-red-700">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <div x-show="banners.length === 0" class="col-span-full text-center py-16 text-sm text-gray-500 bg-white rounded-xl border border-dashed border-gray-300">
            No banners yet. Add one to display a hero slider on the home page.
        </div>
    </div>

    {{-- Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="closeModal()" style="display:none">
        <div class="bg-white rounded-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-semibold mb-4" x-text="form.id ? 'Edit banner' : 'New banner'"></h2>
            <form @submit.prevent="submit()" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image *</label>
                    <div class="flex items-start gap-3">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" class="w-40 aspect-[5/2] object-cover rounded-lg bg-gray-100 border border-gray-200">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="w-40 aspect-[5/2] rounded-lg bg-gradient-to-br from-indigo-50 to-pink-50 flex items-center justify-center text-gray-400 text-xs border border-dashed border-gray-300">No image</div>
                        </template>
                        <div class="flex-1 space-y-2">
                            <label class="cursor-pointer inline-flex items-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-2 rounded-lg text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span x-text="imageFile ? imageFile.name : (form.id ? 'Replace image' : 'Choose image')"></span>
                                <input type="file" accept="image/png,image/jpeg,image/webp" @change="onImageChange($event)" class="hidden">
                            </label>
                            <p class="text-xs text-gray-500">JPG/PNG/WEBP · max 4 MB · recommended 1600x640</p>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" x-model="form.title" maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                    <input type="text" x-model="form.subtitle" maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link URL</label>
                    <input type="text" x-model="form.link_url" placeholder="/categories/electronics or https://..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
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
                        <input type="number" x-model.number="form.sort_order" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Starts at</label>
                        <input type="datetime-local" x-model="form.starts_at" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ends at</label>
                        <input type="datetime-local" x-model="form.ends_at" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div class="flex gap-2 pt-3">
                    <button type="submit" :disabled="saving" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                        <span x-show="!saving" x-text="form.id ? 'Save changes' : 'Create banner'"></span>
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
    function adminBanners() {
        return {
            banners: [],
            loading: true,
            modalOpen: false,
            saving: false,
            form: { id: null, title: '', subtitle: '', link_url: '', status: 'active', sort_order: 0, starts_at: '', ends_at: '' },
            imageFile: null,
            imagePreview: null,
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/admin/promo-banners');
                    this.banners = data.promo_banners || [];
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            openCreate() {
                this.form = { id: null, title: '', subtitle: '', link_url: '', status: 'active', sort_order: 0, starts_at: '', ends_at: '' };
                this.imageFile = null; this.imagePreview = null;
                this.modalOpen = true;
            },
            openEdit(b) {
                const toLocal = (iso) => iso ? new Date(iso).toISOString().slice(0, 16) : '';
                this.form = {
                    id: b.id, title: b.title || '', subtitle: b.subtitle || '',
                    link_url: b.link_url || '', status: b.status, sort_order: b.sort_order || 0,
                    starts_at: toLocal(b.starts_at), ends_at: toLocal(b.ends_at),
                };
                this.imageFile = null;
                this.imagePreview = b.image_url || null;
                this.modalOpen = true;
            },
            closeModal() { this.modalOpen = false; },
            onImageChange(e) {
                const f = e.target.files?.[0];
                if (!f) return;
                if (f.size > 4 * 1024 * 1024) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Image must be 4 MB or smaller.' } }));
                    e.target.value = '';
                    return;
                }
                this.imageFile = f;
                this.imagePreview = URL.createObjectURL(f);
            },
            async submit() {
                if (!this.form.id && !this.imageFile) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Please choose an image.' } }));
                    return;
                }
                this.saving = true;
                try {
                    const fd = new FormData();
                    ['title','subtitle','link_url','status','starts_at','ends_at'].forEach(k => {
                        if (this.form[k]) fd.append(k, this.form[k]);
                    });
                    fd.append('sort_order', this.form.sort_order ?? 0);
                    if (this.imageFile) fd.append('image', this.imageFile);
                    const url = this.form.id ? '/admin/promo-banners/' + this.form.id : '/admin/promo-banners';
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
            async remove(b) {
                if (!confirm('Delete this banner?')) return;
                try {
                    await window.api.delete('/admin/promo-banners/' + b.id);
                    this.banners = this.banners.filter(x => x.id !== b.id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Banner deleted.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
