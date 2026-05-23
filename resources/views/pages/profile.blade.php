@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="profilePage()" x-init="fetch">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6" data-i18n="profile.title">My Profile</h1>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading...</div>

    <div x-show="!loading" class="space-y-6" style="display:none">
        {{-- Avatar card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-medium text-gray-900 mb-4" data-i18n="profile.photo">Profile photo</h2>
            <div class="flex items-center gap-5">
                <div class="relative shrink-0">
                    <div class="w-20 h-20 rounded-full bg-indigo-100 overflow-hidden border-2 border-white shadow">
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" class="w-full h-full object-cover" alt="Avatar">
                        </template>
                        <template x-if="!avatarPreview">
                            <div class="w-full h-full flex items-center justify-center text-indigo-700 text-2xl font-bold"
                                x-text="(form.name || '?').charAt(0).toUpperCase()"></div>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="cursor-pointer inline-flex items-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-4 py-2 rounded-lg text-sm font-medium">
                        <x-heroicon-o-camera class="w-4 h-4"/>
                        <span x-text="uploadingAvatar ? 'Uploading...' : 'Change photo'"></span>
                        <input type="file" accept="image/png,image/jpeg,image/webp" @change="uploadAvatar($event)" class="hidden" :disabled="uploadingAvatar">
                    </label>
                    <p class="text-xs text-gray-500 mt-1.5">JPG, PNG or WEBP · max 4 MB</p>
                </div>
            </div>
        </div>

        {{-- Profile info card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-medium text-gray-900 mb-4" data-i18n="profile.account_details">Account details</h2>
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
</div>

@push('scripts')
<script>
    function profilePage() {
        return {
            loading: true,
            saving: false,
            uploadingAvatar: false,
            avatarPreview: null,
            form: { name: '', email: '', phone: '', bio: '' },
            async fetch() {
                try {
                    const { data } = await window.api.get('/me');
                    const u = data.user || data;
                    this.form.name = u.name || '';
                    this.form.email = u.email || '';
                    this.form.phone = u.phone || '';
                    this.form.bio = u.profile?.bio || '';
                    this.avatarPreview = u.avatar_url || u.profile?.avatar_url || null;
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
            async uploadAvatar(event) {
                const file = event.target.files?.[0];
                if (!file) return;
                this.uploadingAvatar = true;
                try {
                    const preview = URL.createObjectURL(file);
                    this.avatarPreview = preview;
                    const fd = new FormData();
                    fd.append('image', file);
                    const { data } = await window.api.post('/me/avatar', fd, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    });
                    const u = data.user || data;
                    this.avatarPreview = u.avatar_url || u.profile?.avatar_url || preview;
                    Alpine.store('auth').setSession(window.auth.token(), u);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Avatar updated.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.uploadingAvatar = false;
                    event.target.value = '';
                }
            },
        };
    }
</script>
@endpush
@endsection
