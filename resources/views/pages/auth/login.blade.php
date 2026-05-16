@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
<div class="min-h-[calc(100vh-12rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8"
             x-data="loginForm()" x-init="checkRedirect()">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Welcome back</h1>
                <p class="text-sm text-gray-500 mt-1">Sign in to continue shopping</p>
            </div>

            <div x-show="expiredNotice" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800" style="display:none">
                Your session expired. Please sign in again.
            </div>

            <div x-show="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800" style="display:none">
                <span x-text="error"></span>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" x-model="form.email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <a href="{{ route('forgot-password') }}" class="text-sm text-indigo-600 hover:text-indigo-700">Forgot?</a>
                    </div>
                    <input type="password" x-model="form.password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading">Sign in</span>
                    <span x-show="loading" style="display:none">Signing in...</span>
                </button>
            </form>

            <p class="text-center text-sm text-gray-600 mt-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Sign up</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function loginForm() {
        return {
            form: { email: '', password: '' },
            loading: false,
            error: '',
            expiredNotice: false,
            checkRedirect() {
                const params = new URLSearchParams(window.location.search);
                if (params.get('expired')) this.expiredNotice = true;
                if (window.auth.isLoggedIn()) window.location.href = '/';
            },
            async submit() {
                this.error = '';
                this.loading = true;
                try {
                    const { data } = await window.api.post('/auth/login', this.form);
                    Alpine.store('auth').setSession(data.token, data.user);
                    const params = new URLSearchParams(window.location.search);
                    const next = params.get('next');
                    window.location.href = next || '/';
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
