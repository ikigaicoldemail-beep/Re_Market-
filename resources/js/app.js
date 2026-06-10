import './bootstrap';
import './i18n';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import Alpine from 'alpinejs';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

window.Alpine = Alpine;
window.L = L;

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

    Alpine.store('compare', {
        ids: (() => {
            try {
                const raw = localStorage.getItem('compare_ids') || '[]';
                const arr = JSON.parse(raw);
                return Array.isArray(arr) ? arr.map(Number).filter(Boolean) : [];
            } catch {
                return [];
            }
        })(),
        get count() { return this.ids.length; },
        has(id) { return this.ids.includes(Number(id)); },
        toggle(id) {
            const n = Number(id);
            if (!n) return;
            const idx = this.ids.indexOf(n);
            if (idx >= 0) {
                this.ids.splice(idx, 1);
            } else {
                if (this.ids.length >= 4) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'You can compare up to 4 products at once.' } }));
                    return;
                }
                this.ids.push(n);
            }
            this.persist();
        },
        remove(id) {
            this.ids = this.ids.filter(x => x !== Number(id));
            this.persist();
        },
        clear() {
            this.ids = [];
            this.persist();
        },
        persist() {
            try {
                localStorage.setItem('compare_ids', JSON.stringify(this.ids));
            } catch {}
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
