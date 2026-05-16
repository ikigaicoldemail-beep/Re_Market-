@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="profilePage()" x-init="fetch">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">My Profile</h1>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading...</div>

    <div x-show="!loading" class="bg-white rounded-xl border border-gray-200 p-6" style="display:none">
        <form @submit.prevent="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                <input type="text" x-model="form.name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" x-model="form.email" disabled
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                <p class="text-xs text-gray-500 mt-1">Contact support to change your email.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="tel" x-model="form.phone"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                <textarea x-model="form.bio" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <button type="submit" :disabled="saving"
                class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                <span x-show="!saving">Save changes</span>
                <span x-show="saving" style="display:none">Saving...</span>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function profilePage() {
        return {
            loading: true,
            saving: false,
            form: { name: '', email: '', phone: '', bio: '' },
            async fetch() {
                try {
                    const { data } = await window.api.get('/me');
                    const u = data.user || data;
                    this.form.name = u.name || '';
                    this.form.email = u.email || '';
                    this.form.phone = u.phone || '';
                    this.form.bio = u.profile?.bio || '';
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            async save() {
                this.saving = true;
                try {
                    const payload = { name: this.form.name, phone: this.form.phone, bio: this.form.bio };
                    const { data } = await window.api.put('/me', payload);
                    const u = data.user || data;
                    Alpine.store('auth').setSession(window.auth.token(), u);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Profile updated.' } }));
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
