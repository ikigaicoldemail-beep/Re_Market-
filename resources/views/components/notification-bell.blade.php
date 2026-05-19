<div class="relative" x-data="notificationBell()" x-init="init">
    <button @click="toggle()" class="relative p-2 text-gray-700 hover:text-indigo-600" title="Notifications">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"
              class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-semibold rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center"
              style="display:none"></span>
    </button>

    <div x-show="open" @click.outside="open = false" x-transition
         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white border border-gray-200 rounded-lg shadow-lg z-50 flex flex-col max-h-[70vh]"
         style="display:none">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 shrink-0">
            <h3 class="font-semibold text-gray-900">Notifications</h3>
            <button @click="markAllRead()" :disabled="unreadCount === 0"
                class="text-xs text-indigo-600 hover:text-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Mark all read
            </button>
        </div>

        <div class="flex-1 overflow-y-auto">
            <div x-show="loading" class="text-center py-8 text-sm text-gray-500">Loading...</div>
            <div x-show="!loading && notifications.length === 0" class="text-center py-10 text-sm text-gray-500" style="display:none">
                <p>You're all caught up.</p>
            </div>
            <template x-for="n in notifications" :key="n.id">
                <a :href="n.data?.url || '#'" @click.prevent="handleClick(n)"
                    class="block px-4 py-3 border-b border-gray-100 hover:bg-gray-50"
                    :class="!n.read_at ? 'bg-indigo-50/50' : ''">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm shrink-0"
                             :class="iconBg(n.data?.type)" x-text="iconChar(n.data?.type)"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 line-clamp-2" x-text="n.data?.title || 'Notification'"></p>
                            <p x-show="n.data?.body" class="text-xs text-gray-500 line-clamp-2 mt-0.5" x-text="n.data?.body" style="display:none"></p>
                            <p class="text-[10px] text-gray-400 mt-1" x-text="formatRelativeTime(n.created_at)"></p>
                        </div>
                        <span x-show="!n.read_at" class="w-2 h-2 bg-indigo-600 rounded-full mt-1 shrink-0" style="display:none"></span>
                    </div>
                </a>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function notificationBell() {
        return {
            open: false,
            loading: false,
            notifications: [],
            unreadCount: 0,
            pollHandle: null,
            init() {
                this.refreshCount();
                this.pollHandle = setInterval(() => this.refreshCount(), 60000);
                window.addEventListener('beforeunload', () => clearInterval(this.pollHandle));
            },
            async toggle() {
                this.open = !this.open;
                if (this.open) await this.fetch();
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/notifications', { params: { per_page: 20 } });
                    this.notifications = data.notifications || [];
                    this.unreadCount = data.unread_count || 0;
                } catch {} finally {
                    this.loading = false;
                }
            },
            async refreshCount() {
                if (!window.auth.isLoggedIn()) return;
                try {
                    const { data } = await window.api.get('/notifications/unread-count');
                    this.unreadCount = data.unread_count || 0;
                } catch {}
            },
            async markAllRead() {
                try {
                    await window.api.post('/notifications/mark-all-read');
                    this.notifications = this.notifications.map(n => ({ ...n, read_at: n.read_at || new Date().toISOString() }));
                    this.unreadCount = 0;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            async handleClick(n) {
                if (!n.read_at) {
                    try {
                        await window.api.post('/notifications/' + n.id + '/read');
                        n.read_at = new Date().toISOString();
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    } catch {}
                }
                if (n.data?.url) window.location.href = n.data.url;
            },
            iconChar(type) {
                const map = {
                    'chat.message': '💬',
                    'order.cancelled': '❌',
                    'product.reviewed': '⭐',
                    'store.followed': '➕',
                };
                return map[type] || '🔔';
            },
            iconBg(type) {
                const map = {
                    'chat.message': 'bg-blue-100',
                    'order.cancelled': 'bg-red-100',
                    'product.reviewed': 'bg-yellow-100',
                    'store.followed': 'bg-green-100',
                };
                return map[type] || 'bg-gray-100';
            },
        };
    }
</script>
@endpush
