@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="min-h-[calc(100vh-12rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8" x-data="resetForm()" x-init="init">
            <h1 class="text-2xl font-semibold text-gray-900 text-center mb-6">Set a new password</h1>

            <div x-show="message" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800" style="display:none">
                <span x-text="message"></span>
            </div>
            <div x-show="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800" style="display:none">
                <span x-text="error"></span>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" x-model="form.email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                    <input type="password" x-model="form.password" required minlength="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                    <input type="password" x-model="form.password_confirmation" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" :disabled="loading"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span x-show="!loading">Reset password</span>
                    <span x-show="loading" style="display:none">Resetting...</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function resetForm() {
        return {
            form: { token: '', email: '', password: '', password_confirmation: '' },
            loading: false,
            message: '',
            error: '',
            init() {
                const params = new URLSearchParams(window.location.search);
                this.form.token = params.get('token') || '';
                this.form.email = params.get('email') || '';
            },
            async submit() {
                this.message = ''; this.error = '';
                this.loading = true;
                try {
                    const { data } = await window.api.post('/auth/reset-password', this.form);
                    this.message = data.message || 'Password reset. Redirecting to sign in...';
                    setTimeout(() => window.location.href = '/login', 1500);
                } catch (e) {
                    this.error = window.extractApiError(e);
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
@endpush
@endsection
