@extends('layouts.app')

@section('title', 'Conversation')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="conversationThread()" x-init="init">
    <a href="/messages" class="inline-flex items-center text-sm text-gray-500 hover:text-indigo-600 mb-3">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        All conversations
    </a>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading...</div>

    <div x-show="!loading && error" class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 text-sm" x-text="error" style="display:none"></div>

    <div x-show="!loading && conversation && !error" class="bg-white rounded-xl border border-gray-200 flex flex-col" style="display:none; height: calc(100vh - 12rem); min-height: 500px;">
        {{-- Header --}}
        <div class="flex items-center gap-3 p-4 border-b border-gray-200">
            <div class="w-10 h-10 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-semibold shrink-0"
                x-text="otherInitial()"></div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 truncate" x-text="otherName()"></p>
                <p class="text-xs text-gray-500 truncate" x-show="conversation?.product" style="display:none">
                    🛍 <a :href="'/products/' + conversation?.product?.id" class="hover:underline" x-text="conversation?.product?.title"></a>
                </p>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3" x-ref="scrollArea">
            <template x-if="messages.length === 0">
                <p class="text-center text-sm text-gray-400 py-8">No messages yet. Say hi!</p>
            </template>
            <template x-for="msg in messages" :key="msg.id">
                <div class="flex" :class="isMine(msg) ? 'justify-end' : 'justify-start'">
                    <div :class="isMine(msg) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-900'"
                        class="max-w-[75%] rounded-2xl px-3 py-2">
                        <template x-if="msg.attachment_url">
                            <a :href="msg.attachment_url" target="_blank" class="block mb-1">
                                <img :src="msg.attachment_url" class="rounded-xl max-h-64 object-cover">
                            </a>
                        </template>
                        <p class="text-sm whitespace-pre-line break-words px-1"
                            x-show="msg.body && msg.body !== '[image]'"
                            x-text="msg.body" style="display:none"></p>
                        <p class="text-[10px] mt-1 opacity-70 px-1" x-text="formatTime(msg.sent_at)"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Composer --}}
        <div class="border-t border-gray-200 p-3">
            <div x-show="pendingFilePreview" class="mb-2 inline-block relative" style="display:none">
                <img :src="pendingFilePreview" class="h-24 rounded-lg object-cover">
                <button type="button" @click="clearAttachment()"
                    class="absolute -top-2 -right-2 w-6 h-6 bg-gray-900 text-white rounded-full text-xs flex items-center justify-center hover:bg-black">×</button>
            </div>
            <form @submit.prevent="send()" class="flex items-end gap-2">
                <label class="cursor-pointer p-2 text-gray-500 hover:text-indigo-600" title="Attach image">
                    <input type="file" accept="image/*" class="hidden" @change="onAttach($event)" x-ref="fileInput">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                </label>
                <textarea x-model="draft" rows="1"
                    @keydown.enter.exact.prevent="send()"
                    @input="autoResize($event)"
                    placeholder="Type a message..."
                    class="flex-1 resize-none border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    style="max-height: 120px;"></textarea>
                <button type="submit" :disabled="(!draft.trim() && !pendingFile) || sending"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span x-show="!sending">Send</span>
                    <span x-show="sending" style="display:none">...</span>
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
            async init() {
                const segments = window.location.pathname.split('/').filter(Boolean);
                this.conversationId = parseInt(segments[segments.length - 1]);
                await this.fetchAll();
                this.subscribeRealtime();
                // Poll as a safety net. Aggressive 5s when realtime is off, gentle 30s when it's live.
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
                if (this.messages.some(m => m.id === msg.id)) return; // dedupe
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
                    this.messages.push(resp.data.chat_message);
                    this.lastMessageId = resp.data.chat_message.id;
                    this.draft = '';
                    this.clearAttachment();
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
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Image must be under 5MB.' } }));
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
                } catch {}
            },
            isMine(msg) {
                const me = window.auth.user();
                return me && msg.sender_id === me.id;
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
            formatTime(ts) {
                if (!ts) return '';
                const date = new Date(ts);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },
            scrollToBottom() {
                const el = this.$refs.scrollArea;
                if (el) el.scrollTop = el.scrollHeight;
            },
            autoResize(e) {
                e.target.style.height = 'auto';
                e.target.style.height = Math.min(e.target.scrollHeight, 120) + 'px';
            },
        };
    }
</script>
@endpush
@endsection
