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
            <form @submit.prevent="save" class="space-y-4">
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
                country_code: '', state: '', city: '',
                status: 'active',
            },
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
                    country_code: store.country_code || '',
                    state: store.state || '',
                    city: store.city || '',
                    status: store.status || 'active',
                };
            },
            async save() {
                this.saving = true;
                try {
                    const payload = { ...this.form };
                    if (payload.country_code) payload.country_code = payload.country_code.toUpperCase();
                    Object.keys(payload).forEach(k => { if (payload[k] === '') payload[k] = null; });

                    let data;
                    if (this.hasStore) {
                        ({ data } = await window.api.put('/stores/' + this.store.id, payload));
                    } else {
                        ({ data } = await window.api.post('/stores', payload));
                        this.hasStore = true;
                    }
                    this.store = data.store;
                    this.populateForm(data.store);
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
