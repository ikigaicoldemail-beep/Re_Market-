@extends('layouts.app')

@section('title', 'Messages')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="messagesIndex()" x-init="init">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900" data-i18n="messages.title">Messages</h1>
            <p class="text-sm text-gray-500 mt-0.5" data-i18n="messages.subtitle">Chat with buyers and sellers.</p>
        </div>
        <a href="{{ route('home') }}"
            class="inline-flex items-center gap-2 text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <x-heroicon-o-plus class="w-4 h-4"/>
            Browse listings
        </a>
    </div>

    {{-- Search --}}
    <div class="relative mb-4">
        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
        <input type="text" x-model="search" placeholder="Search by name or product…"
            class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
        <button x-show="search" @click="search = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" style="display:none">
            <x-heroicon-m-x-mark class="w-4 h-4"/>
        </button>
    </div>

    {{-- Loading skeleton --}}
    <div x-show="loading && conversations.length === 0" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        <template x-for="i in [1,2,3,4]" :key="i">
            <div class="flex items-center gap-4 p-4 animate-pulse">
                <div class="w-12 h-12 bg-gray-200 rounded-full shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-3.5 bg-gray-200 rounded w-1/3"></div>
                    <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty state --}}
    <div x-show="!loading && filtered.length === 0 && conversations.length === 0"
        class="bg-white rounded-xl border border-gray-200 p-12 text-center" style="display:none">
        <div class="w-16 h-16 mx-auto mb-4 bg-indigo-50 rounded-full flex items-center justify-center">
            <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 text-indigo-400"/>
        </div>
        <p class="font-medium text-gray-700 mb-1">No conversations yet</p>
        <p class="text-sm text-gray-400 mb-5">Find a product and tap <strong>Chat with Seller</strong> to get started.</p>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700">
            <x-heroicon-o-magnifying-glass class="w-4 h-4"/>
            Browse listings
        </a>
    </div>

    {{-- No search results --}}
    <div x-show="!loading && filtered.length === 0 && conversations.length > 0"
        class="bg-white rounded-xl border border-gray-200 p-8 text-center" style="display:none">
        <p class="text-sm text-gray-500">No conversations match "<span x-text="search" class="font-medium"></span>"</p>
    </div>

    {{-- Conversation list --}}
    <div x-show="filtered.length > 0" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 shadow-sm" style="display:none">
        <template x-for="conv in filtered" :key="conv.id">
            <a :href="'/messages/' + conv.id" class="flex items-center gap-3 p-4 hover:bg-gray-50 transition group">
                {{-- Avatar --}}
                <div class="relative shrink-0">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-semibold text-lg"
                        x-text="otherInitial(conv)"></div>
                    <span x-show="conv.unread_count > 0"
                        class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-indigo-600 border-2 border-white rounded-full"
                        style="display:none"></span>
                </div>

                {{-- Body --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 mb-0.5">
                        <p class="font-semibold text-gray-900 truncate text-sm group-hover:text-indigo-700"
                            x-text="otherName(conv)"></p>
                        <span class="text-xs text-gray-400 shrink-0" x-text="formatTime(conv.last_message_at)"></span>
                    </div>
                    <p class="text-sm truncate"
                        :class="conv.unread_count > 0 ? 'text-gray-900 font-medium' : 'text-gray-500'"
                        x-text="conv.last_message?.body && conv.last_message.body !== '[image]'
                            ? conv.last_message.body
                            : (conv.last_message?.type === 'image' ? '📷 Image' : (conv.product ? '🛍 ' + conv.product.title : 'No messages yet'))">
                    </p>
                </div>

                {{-- Product thumbnail --}}
                <template x-if="conv.product && productThumb(conv)">
                    <div class="shrink-0 ml-1">
                        <img :src="productThumb(conv)" :alt="conv.product.title"
                            class="w-12 h-12 rounded-lg object-cover border border-gray-200"
                            onerror="this.parentElement.style.display='none'">
                    </div>
                </template>

                {{-- Unread badge --}}
                <span x-show="conv.unread_count > 0"
                    x-text="conv.unread_count > 9 ? '9+' : conv.unread_count"
                    class="bg-indigo-600 text-white text-xs font-semibold rounded-full min-w-[20px] h-5 px-1.5 flex items-center justify-center shrink-0"
                    style="display:none"></span>
            </a>
        </template>
    </div>

    {{-- Pagination --}}
    <div x-show="meta.last_page > 1" class="flex items-center justify-center gap-2 mt-5" style="display:none">
        <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40">Previous</button>
        <span class="text-sm text-gray-600">Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span></span>
        <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40">Next</button>
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
            search: '',
            pollHandle: null,
            init() {
                this.fetch();
                this.pollHandle = setInterval(() => this.fetch(true), 15000);
                window.addEventListener('beforeunload', () => clearInterval(this.pollHandle));
            },
            get filtered() {
                if (!this.search.trim()) return this.conversations;
                const q = this.search.toLowerCase();
                return this.conversations.filter(conv => {
                    const name = this.otherName(conv).toLowerCase();
                    const product = (conv.product?.title || '').toLowerCase();
                    return name.includes(q) || product.includes(q);
                });
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
                return this.otherName(conv).charAt(0).toUpperCase();
            },
            productThumb(conv) {
                const imgs = conv.product?.images;
                if (!imgs || !imgs.length) return null;
                const primary = imgs.find(i => i.is_primary) || imgs[0];
                return primary?.urls?.thumb_webp || primary?.urls?.thumb || primary?.url || null;
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
