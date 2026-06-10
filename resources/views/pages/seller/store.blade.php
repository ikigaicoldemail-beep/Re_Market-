@extends('layouts.app')

@section('title', 'My Store')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="storePage()" x-init="fetch">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2">My Store</h1>
    <p class="text-sm text-gray-500 mb-6">Your storefront — buyers can browse all your products on one page.</p>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading...</div>

    <div x-show="!loading" style="display:none">
        {{-- Empty state: create store --}}
        <div x-show="!hasStore" class="bg-white rounded-xl border border-gray-200 p-8 text-center" style="display:none">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h18v4H3V3zm0 6h18v12H3V9z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Create your store</h2>
            <p class="text-sm text-gray-500 mb-4">You need a store before you can list products for sale.</p>
        </div>

        {{-- Public URL banner --}}
        <div x-show="hasStore && store?.id" class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg p-4 flex items-center justify-between" style="display:none">
            <div>
                <p class="text-xs text-indigo-600 font-medium uppercase tracking-wider">Your public page</p>
                <p class="text-sm text-gray-900 font-medium mt-0.5" x-text="storeUrl()"></p>
            </div>
            <div class="flex gap-2">
                <a :href="'/stores/' + store.id" target="_blank"
                    class="text-sm bg-white border border-indigo-300 text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-50">View</a>
                <button @click="copyStoreUrl()" class="text-sm bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700">Copy link</button>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <form @submit.prevent="save" class="space-y-5">
                {{-- Banner --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Banner image</label>
                    <div class="relative h-40 sm:h-48 bg-linear-to-br from-indigo-100 to-purple-100 rounded-lg overflow-hidden border border-gray-200">
                        <template x-if="bannerPreview">
                            <img :src="bannerPreview" alt="Banner preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!bannerPreview">
                            <div class="absolute inset-0 flex items-center justify-center text-sm text-indigo-400">No banner uploaded</div>
                        </template>
                    </div>
                    <div class="flex items-center gap-3 mt-2">
                        <label class="cursor-pointer inline-flex items-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-4 py-2 rounded-lg text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span x-text="bannerFile ? bannerFile.name : (form.banner_url ? 'Replace banner' : 'Upload banner')"></span>
                            <input type="file" accept="image/png,image/jpeg,image/webp" @change="onBannerChange($event)" class="hidden">
                        </label>
                        <button type="button" x-show="bannerPreview && (form.banner_url || bannerFile)" @click="removeBanner()"
                            class="text-xs text-red-600 hover:text-red-700" style="display:none">Remove</button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG or WEBP · max 4 MB · recommended 1600×400</p>
                </div>

                {{-- Logo + name row --}}
                <div class="flex items-start gap-4">
                    <div class="shrink-0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                        <div class="w-24 h-24 rounded-2xl bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" alt="Logo preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoPreview">
                                <span class="text-2xl font-bold text-gray-400" x-text="(form.name || '?').charAt(0).toUpperCase()"></span>
                            </template>
                        </div>
                        <div class="mt-2 space-y-1 w-24">
                            <label class="cursor-pointer block w-full text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-2 py-1.5 rounded-md text-xs font-medium">
                                <span x-text="logoFile ? 'Selected ✓' : (form.logo_url ? 'Change' : 'Upload')"></span>
                                <input type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" @change="onLogoChange($event)" class="hidden">
                            </label>
                            <button type="button" x-show="logoPreview && (form.logo_url || logoFile)" @click="removeLogo()"
                                class="text-xs text-red-600 hover:text-red-700 block w-full text-center" style="display:none">Remove</button>
                        </div>
                    </div>

                    <div class="flex-1 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Store name *</label>
                            <input type="text" x-model="form.name" required maxlength="255"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                            <input type="text" x-model="form.slug" maxlength="255" placeholder="my-store-name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">URL-friendly identifier. Leave blank to auto-generate.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="form.description" rows="3" maxlength="5000" placeholder="Tell buyers about your store..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact email</label>
                        <input type="email" x-model="form.contact_email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact phone</label>
                        <input type="tel" x-model="form.contact_phone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telegram link</label>
                        <input type="url" x-model="form.telegram_url" placeholder="https://t.me/yourshop"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Shows a "Chat on Telegram" button to buyers.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Messenger link</label>
                        <input type="url" x-model="form.messenger_url" placeholder="https://m.me/yourpage"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Shows a "Chat on Messenger" button to buyers.</p>
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Country (2-letter)</label>
                        <input type="text" x-model="form.country_code" maxlength="2" placeholder="US"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                        <input type="text" x-model="form.state"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" x-model="form.city"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-gray-700">Map location <span class="text-gray-400">(optional)</span></label>
                        <button type="button" @click="useMyLocation()" :disabled="locatingMe"
                            class="text-xs text-indigo-600 hover:text-indigo-700 font-medium disabled:opacity-50 inline-flex items-center gap-1">
                            <template x-if="!locatingMe">
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-map-pin class="w-3.5 h-3.5"/>
                                    <span>Use my location</span>
                                </span>
                            </template>
                            <span x-show="locatingMe" style="display:none">Locating...</span>
                        </button>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <input type="number" step="0.0000001" x-model.number="form.latitude" placeholder="Latitude (e.g. 11.5564)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <input type="number" step="0.0000001" x-model.number="form.longitude" placeholder="Longitude (e.g. 104.9282)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Right-click a spot on Google Maps and paste the coordinates, or click "Use my location".</p>
                    <div x-show="form.latitude && form.longitude" class="mt-3 rounded-lg overflow-hidden border border-gray-200" style="display:none">
                        <iframe :src="'https://www.google.com/maps?q=' + form.latitude + ',' + form.longitude + '&z=16&output=embed'"
                            width="100%" height="220" frameborder="0" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="form.status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="draft">Draft (not visible to buyers)</option>
                        <option value="active">Active (visible)</option>
                        <option value="suspended">Suspended</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>

                <button type="submit" :disabled="saving"
                    class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span x-show="!saving" x-text="hasStore ? 'Save changes' : 'Create store'"></span>
                    <span x-show="saving" style="display:none">Saving...</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function storePage() {
        return {
            loading: true,
            saving: false,
            hasStore: false,
            store: null,
            form: {
                name: '', slug: '', description: '',
                contact_email: '', contact_phone: '',
                telegram_url: '', messenger_url: '',
                country_code: '', state: '', city: '',
                latitude: '', longitude: '',
                status: 'active',
                logo_url: null, banner_url: null,
            },
            locatingMe: false,
            useMyLocation() {
                if (!navigator.geolocation) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Geolocation not available in this browser.' } }));
                    return;
                }
                this.locatingMe = true;
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.form.latitude = Number(pos.coords.latitude.toFixed(7));
                        this.form.longitude = Number(pos.coords.longitude.toFixed(7));
                        this.locatingMe = false;
                    },
                    (err) => {
                        this.locatingMe = false;
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Could not get your location: ' + err.message } }));
                    },
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            },
            logoFile: null,
            logoPreview: null,
            removeLogoFlag: false,
            bannerFile: null,
            bannerPreview: null,
            removeBannerFlag: false,
            storeUrl() {
                if (!this.store?.id) return '';
                return window.location.origin + '/stores/' + this.store.id;
            },
            async copyStoreUrl() {
                try {
                    await navigator.clipboard.writeText(this.storeUrl());
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Link copied!' } }));
                } catch {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Could not copy.' } }));
                }
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/me/store');
                    if (data.store) {
                        this.store = data.store;
                        this.hasStore = true;
                        this.populateForm(data.store);
                    }
                } catch (e) {
                    if (e?.response?.status !== 404) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    }
                } finally {
                    this.loading = false;
                }
            },
            populateForm(store) {
                this.form = {
                    name: store.name || '',
                    slug: store.slug || '',
                    description: store.description || '',
                    contact_email: store.contact_email || '',
                    contact_phone: store.contact_phone || '',
                    telegram_url: store.telegram_url || '',
                    messenger_url: store.messenger_url || '',
                    country_code: store.country_code || '',
                    state: store.state || '',
                    city: store.city || '',
                    latitude: store.latitude ?? '',
                    longitude: store.longitude ?? '',
                    status: store.status || 'active',
                    logo_url: store.logo_url || null,
                    banner_url: store.banner_url || null,
                };
                this.logoFile = null;
                this.logoPreview = store.logo_url || null;
                this.removeLogoFlag = false;
                this.bannerFile = null;
                this.bannerPreview = store.banner_url || null;
                this.removeBannerFlag = false;
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
            onBannerChange(e) {
                const file = e.target.files?.[0];
                if (!file) return;
                if (file.size > 4 * 1024 * 1024) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Banner must be 4 MB or smaller.' } }));
                    e.target.value = '';
                    return;
                }
                this.bannerFile = file;
                this.bannerPreview = URL.createObjectURL(file);
                this.removeBannerFlag = false;
            },
            removeBanner() {
                this.bannerFile = null;
                this.bannerPreview = null;
                this.removeBannerFlag = true;
            },
            async save() {
                const isCreating = !this.hasStore;
                this.saving = true;
                try {
                    const fd = new FormData();
                    fd.append('name', this.form.name || '');
                    if (this.form.slug) fd.append('slug', this.form.slug);
                    if (this.form.description) fd.append('description', this.form.description);
                    if (this.form.contact_email) fd.append('contact_email', this.form.contact_email);
                    if (this.form.contact_phone) fd.append('contact_phone', this.form.contact_phone);
                    if (this.form.telegram_url) fd.append('telegram_url', this.form.telegram_url);
                    if (this.form.messenger_url) fd.append('messenger_url', this.form.messenger_url);
                    if (this.form.latitude !== '' && this.form.latitude !== null) fd.append('latitude', this.form.latitude);
                    if (this.form.longitude !== '' && this.form.longitude !== null) fd.append('longitude', this.form.longitude);
                    if (this.form.country_code) fd.append('country_code', this.form.country_code.toUpperCase());
                    if (this.form.state) fd.append('state', this.form.state);
                    if (this.form.city) fd.append('city', this.form.city);
                    if (this.form.status) fd.append('status', this.form.status);
                    if (this.logoFile) fd.append('logo', this.logoFile);
                    if (this.removeLogoFlag) fd.append('remove_logo', 1);
                    if (this.bannerFile) fd.append('banner', this.bannerFile);
                    if (this.removeBannerFlag) fd.append('remove_banner', 1);

                    let data;
                    if (this.hasStore) {
                        ({ data } = await window.api.post('/stores/' + this.store.id, fd, {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        }));
                    } else {
                        ({ data } = await window.api.post('/stores', fd, {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        }));
                        this.hasStore = true;
                    }
                    this.store = data.store;
                    this.populateForm(data.store);

                    if (isCreating && data.store?.id) {
                        window.location.href = '/stores/' + data.store.id;
                        return;
                    }

                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message || 'Saved!' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.saving = false;
                }
            },
        };
    }
</script>
@endpush
@endsection
