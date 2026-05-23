@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="min-h-[calc(100vh-12rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8" x-data="forgotForm()">
            <h1 class="text-2xl font-semibold text-gray-900 text-center mb-2" data-i18n="auth.reset_title">Reset your password</h1>
            <p class="text-sm text-gray-500 text-center mb-6" data-i18n="auth.reset_subtitle">Enter your email and we'll send you a reset link.</p>

            <div x-show="message" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800" style="display:none">
                <span x-text="message"></span>
            </div>
            <div x-show="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800" style="display:none">
                <span x-text="error"></span>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n="auth.email">Email</label>
                    <input type="email" x-model="form.email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" :disabled="loading"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span x-show="!loading" data-i18n="auth.send_link">Send reset link</span>
                    <span x-show="loading" data-i18n="auth.sending" style="display:none">Sending...</span>
                </button>
            </form>

            <p class="text-center text-sm text-gray-600 mt-6">
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium" data-i18n="auth.back_to_signin">Back to sign in</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function forgotForm() {
        return {
            form: { email: '' },
            loading: false,
            message: '',
            error: '',
            async submit() {
                this.message = ''; this.error = '';
                this.loading = true;
                try {
                    const { data } = await window.api.post('/auth/forgot-password', this.form);
                    this.message = data.message || 'Reset link sent. Please check your email.';
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
