@extends('layouts.app')

@section('title', 'Conversation')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="conversationThread()" x-init="init">
    <a href="/messages" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 mb-3 group">
        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
</svg>
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
        class="bg-white rounded-2xl border border-gray-200 flex flex-col shadow-sm overflow-hidden"
        style="display:none; height: calc(100vh - 10rem); min-height: 540px;">

        {{-- ── Header ────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-100 bg-white/90 backdrop-blur-sm shrink-0">
            {{-- Avatar with presence dot --}}
            <div class="relative shrink-0">
                <div class="w-11 h-11 bg-gradient-to-br from-indigo-500 to-violet-600 text-white rounded-full flex items-center justify-center font-semibold text-lg shadow-sm ring-2 ring-white"
                    x-text="otherInitial()"></div>
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-white rounded-full"></span>
            </div>

            {{-- Name --}}
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 truncate leading-tight" x-text="otherName()"></p>
                <p class="text-xs text-gray-400 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span>
                    Active now
                </p>
            </div>

            {{-- Product card pill (when product context exists) --}}
            <template x-if="conversation?.product">
                <a :href="'/products/' + conversation.product.id"
                    class="flex items-center gap-2.5 bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-1.5 hover:bg-gray-100 hover:border-gray-300 transition shrink-0 max-w-[220px]">
                    <template x-if="productThumb()">
                        <img :src="productThumb()" class="w-10 h-10 rounded-lg object-cover border border-gray-100 shrink-0">
                    </template>
                    <template x-if="!productThumb()">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
</svg>
                        </div>
                    </template>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-800 truncate" x-text="conversation.product.title"></p>
                        <p class="text-sm text-indigo-600 font-bold leading-tight"
                            x-text="formatPrice(conversation.product.price_amount, conversation.product.currency)"></p>
                    </div>
                </a>
            </template>
        </div>

        {{-- ── Messages ──────────────────────────────────────────────── --}}
        <div class="flex-1 min-h-0 overflow-y-auto px-4 sm:px-6 py-4 space-y-1 bg-gradient-to-b from-gray-50 to-gray-50/40"
            x-ref="scrollArea">
            <template x-if="messages.length === 0">
                <div class="flex flex-col items-center justify-center h-full py-12 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-50 to-violet-100 rounded-2xl flex items-center justify-center mb-4 shadow-sm">
                        <svg class="w-8 h-8 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
</svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-700">Start the conversation</p>
                    <p class="text-xs text-gray-400 mt-1 max-w-xs">Ask about availability, price, or condition — be friendly and clear.</p>
                </div>
            </template>

            <template x-for="(msg, index) in messages" :key="msg.id">
                <div>
                    {{-- Date separator --}}
                    <template x-if="showDateSeparator(index)">
                        <div class="flex justify-center py-3">
                            <span class="text-[11px] text-gray-500 font-medium bg-white border border-gray-200 rounded-full px-3 py-1 shadow-sm"
                                x-text="dateSeparatorLabel(msg.sent_at)"></span>
                        </div>
                    </template>

                    {{-- Message bubble row --}}
                    <div class="flex items-end gap-2" :class="isMine(msg) ? 'justify-end' : 'justify-start'">
                        {{-- Other person's avatar (only show on first in a group) --}}
                        <template x-if="!isMine(msg)">
                            <div class="w-7 h-7 shrink-0 mb-1">
                                <template x-if="showAvatar(index)">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 text-white text-xs font-semibold flex items-center justify-center shadow-sm"
                                        x-text="otherInitial()"></div>
                                </template>
                            </div>
                        </template>

                        <div class="flex flex-col max-w-[75%]" :class="isMine(msg) ? 'items-end' : 'items-start'">
                            {{-- Bubble --}}
                            <div :class="[
                                    isMine(msg)
                                        ? 'bg-indigo-600 text-white rounded-2xl rounded-br-sm'
                                        : 'bg-white text-gray-800 rounded-2xl rounded-bl-sm border border-gray-100',
                                    attachmentUrl(msg) ? 'p-0 overflow-hidden' : 'px-3.5 py-2.5'
                                ]"
                                class="shadow-sm">
                                <template x-if="attachmentUrl(msg)">
                                    <a :href="attachmentUrl(msg)" target="_blank" class="block">
                                        <img :src="attachmentUrl(msg)"
                                            class="block w-full max-w-xs sm:max-w-sm max-h-80 object-cover bg-black/5"
                                            loading="lazy"
                                            x-on:error="$event.target.closest('a')?.classList.add('hidden')">
                                    </a>
                                </template>
                                <p class="text-sm leading-relaxed whitespace-pre-line break-words"
                                    :class="attachmentUrl(msg) ? 'px-3 pt-2 pb-0.5' : ''"
                                    x-show="msg.body && msg.body !== '[image]'"
                                    x-text="msg.body" style="display:none"></p>
                                <p class="text-[10px] opacity-60 text-right"
                                    :class="attachmentUrl(msg) ? 'px-3 pb-2 pt-0.5' : 'mt-1'"
                                    x-text="formatTime(msg.sent_at)"></p>
                            </div>

                            {{-- Seen receipt for my last sent message --}}
                            <template x-if="isMine(msg) && isLastSentMessage(index)">
                                <p class="text-[10px] text-gray-400 mt-1 px-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
  <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/>
</svg>
                                    <span x-text="seenLabel"></span>
                                </p>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- ── Composer ──────────────────────────────────────────────── --}}
        <div class="border-t border-gray-100 px-4 sm:px-5 py-3 bg-white shrink-0">
            {{-- Quick action buttons --}}
            <template x-if="conversation?.product">
                <div class="flex gap-2 mb-2.5">
                    <button @click="insertOffer()"
                        class="text-xs font-medium border border-indigo-200 bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-full hover:bg-indigo-100 transition">
                        💬 Make an offer
                    </button>
                    <button @click="insertQuestion()"
                        class="text-xs font-medium border border-gray-200 bg-gray-50 text-gray-600 px-3 py-1.5 rounded-full hover:bg-gray-100 transition">
                        ❓ Ask a question
                    </button>
                </div>
            </template>

            {{-- Image attachment preview --}}
            <div x-show="pendingFilePreview" class="mb-2.5 inline-block relative" style="display:none">
                <img :src="pendingFilePreview" class="h-24 rounded-xl object-cover border border-gray-200 shadow-sm">
                <button type="button" @click="clearAttachment()"
                    class="absolute -top-2 -right-2 w-5 h-5 bg-gray-800 text-white rounded-full text-xs flex items-center justify-center hover:bg-black shadow">×</button>
            </div>

            <form @submit.prevent="send()" class="flex items-end gap-2">
                {{-- Input pill: attach + textarea --}}
                <div class="flex-1 flex items-end gap-1 bg-gray-100 rounded-2xl pl-2 pr-2.5 py-1 transition focus-within:ring-2 focus-within:ring-indigo-500 focus-within:bg-white">
                    <label class="cursor-pointer p-2 text-gray-400 hover:text-indigo-600 shrink-0 transition" title="Attach image">
                        <input type="file" accept="image/*" class="hidden" @change="onAttach($event)" x-ref="fileInput">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"/>
</svg>
                    </label>

                    <textarea x-model="draft" x-ref="composer" rows="1"
                        @keydown.enter.exact.prevent="send()"
                        @input="autoResize($event)"
                        placeholder="Type a message…"
                        class="flex-1 resize-none bg-transparent border-0 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-0"
                        style="max-height: 120px;"></textarea>
                </div>

                <button type="submit" :disabled="(!draft.trim() && !pendingFile) || sending"
                    class="bg-indigo-600 text-white w-11 h-11 flex items-center justify-center rounded-full hover:bg-indigo-700 disabled:opacity-40 disabled:hover:bg-indigo-600 shrink-0 shadow-sm transition">
                    <template x-if="!sending">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
  <path d="M3.105 2.288a.75.75 0 0 0-.826.95l1.414 4.926A1.5 1.5 0 0 0 5.135 9.25h6.115a.75.75 0 0 1 0 1.5H5.135a1.5 1.5 0 0 0-1.442 1.086l-1.414 4.926a.75.75 0 0 0 .826.95 28.897 28.897 0 0 0 15.293-7.155.75.75 0 0 0 0-1.114A28.897 28.897 0 0 0 3.105 2.288Z"/>
</svg>
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
                        // Let axios/the browser set Content-Type so the multipart
                        // boundary is included — otherwise the upload can't be parsed.
                        resp = await window.api.post('/conversations/' + this.conversationId + '/messages', form);
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

            attachmentUrl(msg) {
                if (!msg) return null;
                if (msg.attachment_url) return msg.attachment_url;
                if (!msg.attachment_path) return null;
                return '/storage/' + String(msg.attachment_path).replace(/^\/+/, '');
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
