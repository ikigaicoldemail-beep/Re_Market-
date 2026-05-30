@extends('layouts.app')

@section('title', 'Conversation')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="conversationThread()" x-init="init">
    <a href="/messages" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 mb-3 group">
        <x-heroicon-o-chevron-left class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform"/>
        All conversations
    </a>

    <div x-show="loading" class="bg-white rounded-xl border border-gray-200 p-8">
        <div class="animate-pulse space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                <div class="space-y-2 flex-1">
                    <div class="h-3.5 bg-gray-200 rounded w-1/4"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="!loading && error" class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 text-sm" x-text="error" style="display:none"></div>

    <div x-show="!loading && conversation && !error"
        class="bg-white rounded-xl border border-gray-200 flex flex-col shadow-sm"
        style="display:none; height: calc(100vh - 11rem); min-height: 520px;">

        {{-- ── Header ────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-200">
            {{-- Avatar --}}
            <div class="w-10 h-10 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-semibold shrink-0"
                x-text="otherInitial()"></div>

            {{-- Name --}}
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 truncate" x-text="otherName()"></p>
                <p class="text-xs text-gray-400">Tap a product below to view it</p>
            </div>

            {{-- Product card pill (when product context exists) --}}
            <template x-if="conversation?.product">
                <a :href="'/products/' + conversation.product.id"
                    class="flex items-center gap-2 border border-gray-200 rounded-lg px-2 py-1.5 hover:bg-gray-50 shrink-0 max-w-[200px]">
                    <template x-if="productThumb()">
                        <img :src="productThumb()" class="w-9 h-9 rounded object-cover border border-gray-100 shrink-0">
                    </template>
                    <template x-if="!productThumb()">
                        <div class="w-9 h-9 rounded bg-gray-100 flex items-center justify-center shrink-0">
                            <x-heroicon-o-photo class="w-4 h-4 text-gray-400"/>
                        </div>
                    </template>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-800 truncate" x-text="conversation.product.title"></p>
                        <p class="text-xs text-indigo-600 font-semibold"
                            x-text="formatPrice(conversation.product.price_amount, conversation.product.currency)"></p>
                    </div>
                </a>
            </template>
        </div>

        {{-- ── Messages ──────────────────────────────────────────────── --}}
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-1" x-ref="scrollArea">
            <template x-if="messages.length === 0">
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-14 h-14 bg-indigo-50 rounded-full flex items-center justify-center mb-3">
                        <x-heroicon-o-chat-bubble-left-right class="w-7 h-7 text-indigo-400"/>
                    </div>
                    <p class="text-sm font-medium text-gray-600">Start the conversation</p>
                    <p class="text-xs text-gray-400 mt-1">Ask about availability, price, or condition.</p>
                </div>
            </template>

            <template x-for="(msg, index) in messages" :key="msg.id">
                <div>
                    {{-- Date separator --}}
                    <template x-if="showDateSeparator(index)">
                        <div class="flex items-center gap-3 py-2 my-1">
                            <div class="flex-1 h-px bg-gray-200"></div>
                            <span class="text-xs text-gray-400 font-medium px-2" x-text="dateSeparatorLabel(msg.sent_at)"></span>
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </div>
                    </template>

                    {{-- Message bubble row --}}
                    <div class="flex items-end gap-2" :class="isMine(msg) ? 'justify-end' : 'justify-start'">
                        {{-- Other person's avatar (only show on first in a group) --}}
                        <template x-if="!isMine(msg)">
                            <div class="w-7 h-7 shrink-0 mb-0.5">
                                <template x-if="showAvatar(index)">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold flex items-center justify-center"
                                        x-text="otherInitial()"></div>
                                </template>
                            </div>
                        </template>

                        <div class="flex flex-col" :class="isMine(msg) ? 'items-end' : 'items-start'">
                            {{-- Bubble --}}
                            <div :class="isMine(msg)
                                    ? 'bg-indigo-600 text-white rounded-2xl rounded-br-md'
                                    : 'bg-gray-100 text-gray-900 rounded-2xl rounded-bl-md'"
                                class="max-w-[75%] px-3.5 py-2.5">
                                <template x-if="msg.attachment_url">
                                    <a :href="msg.attachment_url" target="_blank" class="block mb-1">
                                        <img :src="msg.attachment_url" class="rounded-xl max-h-64 object-cover">
                                    </a>
                                </template>
                                <p class="text-sm whitespace-pre-line break-words"
                                    x-show="msg.body && msg.body !== '[image]'"
                                    x-text="msg.body" style="display:none"></p>
                                <p class="text-[10px] mt-0.5 opacity-60 text-right" x-text="formatTime(msg.sent_at)"></p>
                            </div>

                            {{-- Seen receipt for my last sent message --}}
                            <template x-if="isMine(msg) && isLastSentMessage(index)">
                                <p class="text-[10px] text-gray-400 mt-0.5 px-1" x-text="seenLabel"></p>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- ── Composer ──────────────────────────────────────────────── --}}
        <div class="border-t border-gray-200 p-3">
            {{-- Quick offer button --}}
            <template x-if="conversation?.product">
                <div class="flex gap-2 mb-2">
                    <button @click="insertOffer()"
                        class="text-xs border border-indigo-300 text-indigo-700 px-3 py-1 rounded-full hover:bg-indigo-50 transition">
                        💬 Make an offer
                    </button>
                    <button @click="insertQuestion()"
                        class="text-xs border border-gray-300 text-gray-600 px-3 py-1 rounded-full hover:bg-gray-50 transition">
                        ❓ Ask a question
                    </button>
                </div>
            </template>

            {{-- Image attachment preview --}}
            <div x-show="pendingFilePreview" class="mb-2 inline-block relative" style="display:none">
                <img :src="pendingFilePreview" class="h-24 rounded-lg object-cover border border-gray-200">
                <button type="button" @click="clearAttachment()"
                    class="absolute -top-2 -right-2 w-5 h-5 bg-gray-800 text-white rounded-full text-xs flex items-center justify-center hover:bg-black">×</button>
            </div>

            <form @submit.prevent="send()" class="flex items-end gap-2">
                {{-- Attach image --}}
                <label class="cursor-pointer p-2 text-gray-400 hover:text-indigo-600 shrink-0" title="Attach image">
                    <input type="file" accept="image/*" class="hidden" @change="onAttach($event)" x-ref="fileInput">
                    <x-heroicon-o-paper-clip class="w-5 h-5"/>
                </label>

                <textarea x-model="draft" x-ref="composer" rows="1"
                    @keydown.enter.exact.prevent="send()"
                    @keydown.enter.shift="/* allow newline */"
                    @input="autoResize($event)"
                    placeholder="Type a message… (Enter to send)"
                    class="flex-1 resize-none border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    style="max-height: 120px;"></textarea>

                <button type="submit" :disabled="(!draft.trim() && !pendingFile) || sending"
                    class="bg-indigo-600 text-white p-2.5 rounded-xl hover:bg-indigo-700 disabled:opacity-40 shrink-0 transition">
                    <template x-if="!sending">
                        <x-heroicon-m-paper-airplane class="w-5 h-5"/>
                    </template>
                    <template x-if="sending">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    </template>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function conversationThread() {
        return {
            conversationId: null,
            conversation: null,
            messages: [],
            draft: '',
            loading: true,
            sending: false,
            error: '',
            pollHandle: null,
            lastMessageId: 0,
            pendingFile: null,
            pendingFilePreview: '',
            channel: null,
            realtimeActive: false,
            seenLabel: '',

            async init() {
                const segments = window.location.pathname.split('/').filter(Boolean);
                this.conversationId = parseInt(segments[segments.length - 1]);
                await this.fetchAll();
                this.subscribeRealtime();
                this.pollHandle = setInterval(() => this.poll(), this.realtimeActive ? 30000 : 5000);
                window.addEventListener('beforeunload', () => this.teardown());
            },

            subscribeRealtime() {
                if (!window.realtime?.enabled()) return;
                const ch = window.realtime.privateChannel('conversation.' + this.conversationId);
                if (!ch) return;
                this.channel = ch;
                this.realtimeActive = true;
                ch.listen('.message.sent', (payload) => this.handleIncoming(payload?.chat_message));
            },

            handleIncoming(msg) {
                if (!msg || !msg.id) return;
                if (this.messages.some(m => m.id === msg.id)) return;
                if (msg.id <= this.lastMessageId) return;
                this.messages.push(msg);
                this.lastMessageId = msg.id;
                this.markSeen();
                this.$nextTick(() => this.scrollToBottom());
            },

            teardown() {
                if (this.pollHandle) clearInterval(this.pollHandle);
                if (this.realtimeActive) window.realtime.leave('conversation.' + this.conversationId);
            },

            async fetchAll() {
                this.loading = true;
                try {
                    const [convs, msgs] = await Promise.all([
                        window.api.get('/conversations'),
                        window.api.get('/conversations/' + this.conversationId + '/messages'),
                    ]);
                    this.conversation = (convs.data.conversations || []).find(c => c.id === this.conversationId);
                    if (!this.conversation) {
                        this.error = 'Conversation not found.';
                        return;
                    }
                    this.messages = (msgs.data.messages || []).slice().reverse();
                    this.lastMessageId = this.messages.length ? this.messages[this.messages.length - 1].id : 0;
                    this.updateSeenLabel();
                    await this.markSeen();
                    this.$nextTick(() => this.scrollToBottom());
                } catch (e) {
                    this.error = window.extractApiError(e) || 'Could not load conversation.';
                } finally {
                    this.loading = false;
                }
            },

            async poll() {
                try {
                    const { data } = await window.api.get('/conversations/' + this.conversationId + '/messages');
                    const fetched = (data.messages || []).slice().reverse();
                    const fresh = fetched.filter(m => m.id > this.lastMessageId);
                    if (fresh.length > 0) {
                        this.messages.push(...fresh);
                        this.lastMessageId = this.messages[this.messages.length - 1].id;
                        this.updateSeenLabel();
                        await this.markSeen();
                        this.$nextTick(() => this.scrollToBottom());
                    }
                } catch {}
            },

            async send() {
                const body = this.draft.trim();
                if ((!body && !this.pendingFile) || this.sending) return;
                this.sending = true;
                try {
                    let resp;
                    if (this.pendingFile) {
                        const form = new FormData();
                        if (body) form.append('body', body);
                        form.append('attachment', this.pendingFile);
                        resp = await window.api.post('/conversations/' + this.conversationId + '/messages', form, {
                            headers: { 'Content-Type': 'multipart/form-data' },
                        });
                    } else {
                        resp = await window.api.post('/conversations/' + this.conversationId + '/messages', { body });
                    }
                    const msg = resp.data.chat_message;
                    this.messages.push(msg);
                    this.lastMessageId = msg.id;
                    this.draft = '';
                    this.clearAttachment();
                    this.seenLabel = 'Sent';
                    this.$nextTick(() => this.scrollToBottom());
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.sending = false;
                }
            },

            onAttach(e) {
                const f = e.target.files?.[0];
                if (!f) return;
                if (f.size > 5 * 1024 * 1024) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Image must be under 5 MB.' } }));
                    e.target.value = '';
                    return;
                }
                this.pendingFile = f;
                this.pendingFilePreview = URL.createObjectURL(f);
            },

            clearAttachment() {
                this.pendingFile = null;
                this.pendingFilePreview = '';
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            },

            async markSeen() {
                try {
                    await window.api.post('/conversations/' + this.conversationId + '/seen');
                    this.updateSeenLabel();
                } catch {}
            },

            updateSeenLabel() {
                const me = window.auth.user();
                if (!me || !this.messages.length) return;
                const lastMine = [...this.messages].reverse().find(m => m.sender_id === me.id);
                if (!lastMine) return;
                const other = this.otherParticipant();
                const lastReadId = other?.last_read_message_id ?? 0;
                this.seenLabel = lastReadId >= lastMine.id ? 'Seen' : 'Sent';
            },

            isMine(msg) {
                const me = window.auth.user();
                return me && msg.sender_id === me.id;
            },

            isLastSentMessage(index) {
                const me = window.auth.user();
                if (!me) return false;
                for (let i = this.messages.length - 1; i >= 0; i--) {
                    if (this.messages[i].sender_id === me.id) return i === index;
                }
                return false;
            },

            showDateSeparator(index) {
                if (index === 0) return true;
                const prev = new Date(this.messages[index - 1].sent_at);
                const curr = new Date(this.messages[index].sent_at);
                return prev.toDateString() !== curr.toDateString();
            },

            showAvatar(index) {
                if (index === this.messages.length - 1) return true;
                const next = this.messages[index + 1];
                return this.isMine(next) || next.sender_id !== this.messages[index].sender_id;
            },

            dateSeparatorLabel(ts) {
                if (!ts) return '';
                const date = new Date(ts);
                const now = new Date();
                const yesterday = new Date(now);
                yesterday.setDate(yesterday.getDate() - 1);
                if (date.toDateString() === now.toDateString()) return 'Today';
                if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';
                return date.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
            },

            otherParticipant() {
                const me = window.auth.user();
                const participants = this.conversation?.participants || [];
                return participants.find(p => p.user_id !== me?.id) || participants[0];
            },

            otherName() {
                const other = this.otherParticipant();
                return other?.user?.name || 'User #' + (other?.user_id ?? '?');
            },

            otherInitial() {
                return this.otherName().charAt(0).toUpperCase();
            },

            productThumb() {
                const imgs = this.conversation?.product?.images;
                if (!imgs || !imgs.length) return null;
                const primary = imgs.find(i => i.is_primary) || imgs[0];
                return primary?.urls?.thumb_webp || primary?.urls?.thumb || primary?.url || null;
            },

            formatTime(ts) {
                if (!ts) return '';
                const date = new Date(ts);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },

            formatPrice(amount, currency) {
                if (!amount) return '';
                return window.formatPrice ? window.formatPrice(amount, currency) : `${currency ?? '$'}${amount}`;
            },

            scrollToBottom() {
                const el = this.$refs.scrollArea;
                if (el) el.scrollTop = el.scrollHeight;
            },

            autoResize(e) {
                e.target.style.height = 'auto';
                e.target.style.height = Math.min(e.target.scrollHeight, 120) + 'px';
            },

            insertOffer() {
                const product = this.conversation?.product;
                if (!product) return;
                const price = window.formatPrice ? window.formatPrice(product.price_amount, product.currency) : product.price_amount;
                this.draft = `Hi! I'm interested in "${product.title}". Would you consider ${price}?`;
                this.$nextTick(() => this.$refs.composer?.focus());
            },

            insertQuestion() {
                const product = this.conversation?.product;
                this.draft = product ? `Hi! I have a question about "${product.title}". ` : 'Hi! I have a question. ';
                this.$nextTick(() => this.$refs.composer?.focus());
            },
        };
    }
</script>
@endpush
@endsection
