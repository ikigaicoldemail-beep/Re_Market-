@extends('layouts.app')

@section('title', 'My Addresses')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="addressesPage()" x-init="fetch">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900" data-i18n="addr.title">My Addresses</h1>
        <button @click="showForm = true; resetForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700" data-i18n="addr.add">
            + Add Address
        </button>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading...</div>

    <div x-show="!loading && addresses.length === 0 && !showForm" class="text-center py-20 bg-white rounded-xl border border-gray-200" style="display:none">
        <p class="text-gray-500 mb-4">No saved addresses yet.</p>
        <button @click="showForm = true" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-indigo-700">
            Add your first address
        </button>
    </div>

    <div x-show="!loading && addresses.length > 0" class="grid md:grid-cols-2 gap-4" style="display:none">
        <template x-for="addr in addresses" :key="addr.id">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between mb-2">
                    <p class="font-medium text-gray-900">
                        <span x-text="addr.recipient_name"></span>
                        <span x-show="addr.is_default" class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full ml-2" style="display:none">Default</span>
                    </p>
                </div>
                <div class="text-sm text-gray-600 space-y-0.5">
                    <p x-text="addr.address_line_1"></p>
                    <p x-text="addr.address_line_2" x-show="addr.address_line_2" style="display:none"></p>
                    <p x-text="[addr.city, addr.state, addr.country_code, addr.postal_code].filter(Boolean).join(', ')"></p>
                    <p x-text="addr.phone"></p>
                </div>
                <div class="flex gap-2 mt-4">
                    <button @click="edit(addr)" class="text-sm text-indigo-600 hover:text-indigo-700">Edit</button>
                    <button @click="remove(addr)" class="text-sm text-red-600 hover:text-red-700">Delete</button>
                </div>
            </div>
        </template>
    </div>

    {{-- Add/Edit Form Modal --}}
    <div x-show="showForm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showForm = false" style="display:none">
        <div class="bg-white rounded-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-semibold mb-4" x-text="editingId ? 'Edit Address' : 'Add Address'"></h2>
            <form @submit.prevent="save" class="space-y-3">
                <input type="text" x-model="form.recipient_name" placeholder="Recipient name *" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <input type="tel" x-model="form.phone" placeholder="Phone *" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <input type="text" x-model="form.address_line_1" placeholder="Street address *" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <input type="text" x-model="form.address_line_2" placeholder="Apartment, suite (optional)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" x-model="form.city" placeholder="City *" required
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <input type="text" x-model="form.state" placeholder="State *" required
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" x-model="form.country_code" placeholder="Country (US) *" maxlength="2" required
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase">
                    <input type="text" x-model="form.postal_code" placeholder="Postal code"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" x-model="form.is_default">
                    <span>Set as default</span>
                </label>
                <div class="flex gap-2 pt-2">
                    <button type="submit" :disabled="saving" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                        <span x-show="!saving">Save</span>
                        <span x-show="saving" style="display:none">Saving...</span>
                    </button>
                    <button type="button" @click="showForm = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function addressesPage() {
        return {
            loading: true,
            saving: false,
            addresses: [],
            showForm: false,
            editingId: null,
            form: { recipient_name: '', phone: '', address_line_1: '', address_line_2: '', city: '', state: '', country_code: '', postal_code: '', is_default: false },
            resetForm() {
                this.editingId = null;
                this.form = { recipient_name: '', phone: '', address_line_1: '', address_line_2: '', city: '', state: '', country_code: '', postal_code: '', is_default: false };
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/addresses');
                    this.addresses = data.addresses || [];
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            edit(addr) {
                this.editingId = addr.id;
                this.form = {
                    recipient_name: addr.recipient_name || '',
                    phone: addr.phone || '',
                    address_line_1: addr.address_line_1 || '',
                    address_line_2: addr.address_line_2 || '',
                    city: addr.city || '',
                    state: addr.state || '',
                    country_code: addr.country_code || '',
                    postal_code: addr.postal_code || '',
                    is_default: !!addr.is_default,
                };
                this.showForm = true;
            },
            async save() {
                this.saving = true;
                try {
                    const payload = { ...this.form };
                    payload.country_code = (payload.country_code || '').toUpperCase();
                    if (this.editingId) {
                        await window.api.put('/addresses/' + this.editingId, payload);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Address updated.' } }));
                    } else {
                        await window.api.post('/addresses', payload);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Address added.' } }));
                    }
                    this.showForm = false;
                    await this.fetch();
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.saving = false;
                }
            },
            async remove(addr) {
                if (!confirm('Delete this address?')) return;
                try {
                    await window.api.delete('/addresses/' + addr.id);
                    this.addresses = this.addresses.filter(a => a.id !== addr.id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Address deleted.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
