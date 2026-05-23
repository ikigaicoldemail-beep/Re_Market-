@extends('layouts.app')

@section('title', 'Create an Account')

@section('content')
<div class="min-h-[calc(100vh-12rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8"
             x-data="registerForm()" x-init="if (window.auth.isLoggedIn()) window.location.href = '/'">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900" data-i18n="auth.signup_title">Create your account</h1>
                <p class="text-sm text-gray-500 mt-1" data-i18n="auth.signup_subtitle">Start buying and selling today</p>
            </div>

            <div x-show="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800" style="display:none">
                <span x-text="error"></span>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n="auth.full_name">Full name</label>
                    <input type="text" x-model="form.name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n="auth.email">Email</label>
                    <input type="email" x-model="form.email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n="auth.phone_optional">Phone (optional)</label>
                    <input type="tel" x-model="form.phone"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n="auth.password">Password</label>
                    <input type="password" x-model="form.password" required minlength="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 mt-1" data-i18n="auth.password_hint">Must include letters and numbers, 8+ characters.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" data-i18n="auth.confirm_password">Confirm password</label>
                    <input type="password" x-model="form.password_confirmation" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span x-show="!loading" data-i18n="auth.create_account">Create account</span>
                    <span x-show="loading" data-i18n="auth.creating" style="display:none">Creating...</span>
                </button>
            </form>

            <p class="text-center text-sm text-gray-600 mt-6">
                <span data-i18n="auth.have_account">Already have an account?</span>
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium" data-i18n="nav.signin">Sign in</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function registerForm() {
        return {
            form: { name: '', email: '', phone: '', password: '', password_confirmation: '' },
            loading: false,
            error: '',
            async submit() {
                this.error = '';
                this.loading = true;
                try {
                    const { data } = await window.api.post('/auth/register', this.form);
                    Alpine.store('auth').setSession(data.token, data.user);
                    window.location.href = '/';
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
