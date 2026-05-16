@extends('layouts.app')

@section('title', 'Messages')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="messagesIndex()" x-init="init">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Messages</h1>
            <p class="text-sm text-gray-500 mt-1">Chat with buyers and sellers.</p>
        </div>
        <button @click="refresh()" :disabled="loading"
            class="text-sm border border-gray-300 px-3 py-1.5 rounded-lg hover:bg-gray-50 disabled:opacity-50">
            <span x-show="!loading">Refresh</span>
            <span x-show="loading" style="display:none">Loading...</span>
        </button>
    </div>

    <div x-show="loading && conversations.length === 0" class="text-center py-20 text-gray-500">Loading conversations...</div>

    <div x-show="!loading && conversations.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center" style="display:none">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <p class="text-gray-500">No conversations yet.</p>
        <p class="text-sm text-gray-400 mt-1">Start one from a product page.</p>
    </div>

    <div x-show="conversations.length > 0" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-200" style="display:none">
        <template x-for="conv in conversations" :key="conv.id">
            <a :href="'/messages/' + conv.id" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition">
                <div class="w-12 h-12 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-semibold shrink-0"
                    x-text="otherInitial(conv)"></div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-medium text-gray-900 truncate" x-text="otherName(conv)"></p>
                        <span class="text-xs text-gray-400 shrink-0" x-text="formatTime(conv.last_message_at)"></span>
                    </div>
                    <p class="text-sm text-gray-500 truncate" x-text="conv.last_message?.body || (conv.product ? '🛍 About: ' + conv.product.title : 'No messages yet')"></p>
                </div>
                <span x-show="conv.unread_count > 0"
                    x-text="conv.unread_count"
                    class="bg-indigo-600 text-white text-xs font-medium rounded-full min-w-[20px] h-5 px-1.5 flex items-center justify-center shrink-0"
                    style="display:none"></span>
            </a>
        </template>
    </div>

    <div x-show="meta.last_page > 1" class="flex items-center justify-center gap-2 mt-4" style="display:none">
        <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">Previous</button>
        <span class="text-sm text-gray-600">Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span></span>
        <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">Next</button>
    </div>
</div>

@push('scripts')
<script>
    function messagesIndex() {
        return {
            conversations: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            page: 1,
            loading: true,
            pollHandle: null,
            init() {
                this.fetch();
                this.pollHandle = setInterval(() => this.fetch(true), 15000);
                window.addEventListener('beforeunload', () => clearInterval(this.pollHandle));
            },
            refresh() {
                this.fetch();
            },
            async fetch(silent = false) {
                if (!silent) this.loading = true;
                try {
                    const { data } = await window.api.get('/conversations', { params: { page: this.page } });
                    this.conversations = data.conversations || [];
                    this.meta = data.meta || this.meta;
                } catch (e) {
                    if (!silent) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    }
                } finally {
                    this.loading = false;
                }
            },
            goToPage(page) {
                this.page = page;
                this.fetch();
            },
            otherParticipant(conv) {
                const me = window.auth.user();
                const participants = conv.participants || [];
                return participants.find(p => p.user_id !== me?.id) || participants[0];
            },
            otherName(conv) {
                const other = this.otherParticipant(conv);
                return other?.user?.name || 'User #' + (other?.user_id ?? '?');
            },
            otherInitial(conv) {
                const name = this.otherName(conv);
                return name.charAt(0).toUpperCase();
            },
            formatTime(ts) {
                if (!ts) return '';
                const date = new Date(ts);
                const now = new Date();
                const diffMs = now - date;
                const diffMin = Math.floor(diffMs / 60000);
                if (diffMin < 1) return 'now';
                if (diffMin < 60) return diffMin + 'm';
                if (diffMin < 1440) return Math.floor(diffMin / 60) + 'h';
                if (diffMin < 10080) return Math.floor(diffMin / 1440) + 'd';
                return date.toLocaleDateString();
            },
        };
    }
</script>
@endpush
@endsection
