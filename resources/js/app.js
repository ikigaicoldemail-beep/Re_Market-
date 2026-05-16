import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('auth', {
        token: localStorage.getItem('auth_token'),
        user: (() => {
            try {
                return JSON.parse(localStorage.getItem('auth_user') || 'null');
            } catch {
                return null;
            }
        })(),
        get loggedIn() {
            return !!this.token;
        },
        setSession(token, user) {
            this.token = token;
            this.user = user;
            window.auth.setSession(token, user);
        },
        clearSession() {
            this.token = null;
            this.user = null;
            window.auth.clearSession();
        },
        async logout() {
            await window.auth.logout();
        },
    });

    Alpine.store('cart', {
        count: 0,
        async refresh() {
            if (!window.auth.isLoggedIn()) {
                this.count = 0;
                return;
            }
            try {
                const { data } = await window.api.get('/cart');
                const items = data?.cart?.items || [];
                this.count = items.reduce((sum, item) => sum + (item.quantity || 0), 0);
            } catch {
                this.count = 0;
            }
        },
    });

    Alpine.store('chat', {
        unread: 0,
        async refresh() {
            if (!window.auth.isLoggedIn()) {
                this.unread = 0;
                return;
            }
            try {
                const { data } = await window.api.get('/conversations/unread-count');
                this.unread = Number(data?.unread_count) || 0;
            } catch {
                this.unread = 0;
            }
        },
    });
});

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    if (window.Alpine && Alpine.store('cart')) {
        Alpine.store('cart').refresh();
    }
    if (window.Alpine && Alpine.store('chat')) {
        Alpine.store('chat').refresh();
        setInterval(() => Alpine.store('chat').refresh(), 30000);
    }
});
