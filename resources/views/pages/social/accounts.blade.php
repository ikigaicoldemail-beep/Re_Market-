@extends('layouts.app')

@section('title', 'Social Accounts')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="socialAccounts()" x-init="init">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2">Social Accounts</h1>
    <p class="text-sm text-gray-500 mb-2">Connect Facebook to auto-post and schedule product listings.</p>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6 text-xs text-blue-800">
        <p class="font-medium mb-1">How Facebook Page posting works</p>
        <p>Log in with your <strong>personal account</strong> when prompted &mdash; that's the account that owns/manages your Page. Facebook will return the list of Pages you manage, and we'll link each one so you can auto-post on its behalf. We never post to your personal timeline.</p>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading...</div>

    <div x-show="!loading" class="max-w-md mb-8" style="display:none">
        {{-- Facebook card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-[#1877F2] rounded-full flex items-center justify-center text-white font-bold">f</div>
                <div>
                    <h2 class="font-semibold text-gray-900">Facebook</h2>
                    <p class="text-xs text-gray-500" x-text="fbStatus()"></p>
                </div>
            </div>
            <template x-if="connectedFor('facebook').length > 0">
                <div class="space-y-2 mb-3">
                    <template x-for="acc in connectedFor('facebook')" :key="acc.id">
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg text-sm">
                            <span x-text="acc.provider_account_name || acc.provider_user_id"></span>
                            <button @click="disconnect(acc)" class="text-red-600 hover:text-red-700 text-xs">Disconnect</button>
                        </div>
                    </template>
                </div>
            </template>
            <button @click="connectViaOAuth('facebook')" :disabled="connecting === 'facebook'"
                class="w-full bg-[#1877F2] text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-60">
                <span x-show="connecting !== 'facebook'">Connect Facebook</span>
                <span x-show="connecting === 'facebook'" style="display:none">Opening...</span>
            </button>
            <button @click="openManual('facebook')" class="w-full mt-2 text-xs text-gray-500 hover:text-gray-700">
                Or paste a token manually
            </button>
        </div>

    </div>

    {{-- Manual connect modal (fallback) --}}
    <div x-show="showConnect" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showConnect = false" style="display:none">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <h2 class="text-lg font-semibold mb-1">Manually link <span x-text="connectForm.platform"></span></h2>
            <p class="text-xs text-gray-500 mb-4">
                Use this when you already have an access token from the platform developer console.
            </p>
            <form @submit.prevent="submitConnect" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provider user ID *</label>
                    <input type="text" x-model="connectForm.provider_user_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account name (display)</label>
                    <input type="text" x-model="connectForm.provider_account_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Access token *</label>
                    <textarea x-model="connectForm.access_token" required rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"></textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" :disabled="connectSaving"
                        class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                        <span x-show="!connectSaving">Connect</span>
                        <span x-show="connectSaving" style="display:none">Connecting...</span>
                    </button>
                    <button type="button" @click="showConnect = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function socialAccounts() {
        return {
            accounts: [],
            loading: true,
            connecting: null,
            showConnect: false,
            connectSaving: false,
            connectForm: {
                platform: 'facebook',
                provider_user_id: '',
                provider_account_name: '',
                access_token: '',
            },
            init() {
                this.fetch();
                window.addEventListener('message', (e) => this.onPopupMessage(e));
            },
            connectedFor(platform) {
                return this.accounts.filter(a => a.platform === platform);
            },
            fbStatus() {
                const c = this.connectedFor('facebook');
                return c.length === 0 ? 'Not connected' : c.length + ' account(s) connected';
            },
            ttStatus() {
                return c.length === 0 ? 'Not connected' : c.length + ' account(s) connected';
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/social/accounts');
                    this.accounts = data.social_accounts || [];
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            async connectViaOAuth(platform) {
                this.connecting = platform;
                try {
                    const { data } = await window.api.post('/social/' + platform + '/authorize');
                    const url = data?.authorize_url;
                    if (!url) throw new Error('Authorize URL missing.');
                    window.open(url, 'oauth-popup', 'width=600,height=720,scrollbars=yes');
                } catch (e) {
                    const status = e?.response?.status;
                    if (status === 503) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: {
                            type: 'info',
                            message: platform + ' OAuth is not configured. Use manual token paste below.'
                        } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    }
                } finally {
                    this.connecting = null;
                }
            },
            onPopupMessage(event) {
                if (event.origin !== window.location.origin) return;
                const data = event.data;
                if (!data || data.type !== 'social-oauth') return;
                if (data.success) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Account connected!' } }));
                    this.fetch();
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: data.message || 'OAuth failed.' } }));
                }
            },
            openManual(platform) {
                this.connectForm = { platform, provider_user_id: '', provider_account_name: '', access_token: '' };
                this.showConnect = true;
            },
            async submitConnect() {
                this.connectSaving = true;
                try {
                    const { data } = await window.api.post('/social/accounts', this.connectForm);
                    this.accounts.push(data.social_account);
                    this.showConnect = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Account connected!' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.connectSaving = false;
                }
            },
            async disconnect(acc) {
                if (!confirm(`Disconnect ${acc.provider_account_name || acc.platform}?`)) return;
                try {
                    await window.api.delete('/social/accounts/' + acc.id);
                    this.accounts = this.accounts.filter(a => a.id !== acc.id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Disconnected.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
