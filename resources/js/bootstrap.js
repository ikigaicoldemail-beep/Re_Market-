import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error?.response?.status;
        if (status === 401) {
            const path = window.location.pathname;
            const isAuthPage = path.startsWith('/login') || path.startsWith('/register');
            if (!isAuthPage) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
                window.location.href = '/login?expired=1';
                return new Promise(() => {});
            }
        }
        return Promise.reject(error);
    }
);

window.axios = axios;
window.api = api;

window.auth = {
    token() {
        return localStorage.getItem('auth_token');
    },
    user() {
        const raw = localStorage.getItem('auth_user');
        try {
            return raw ? JSON.parse(raw) : null;
        } catch {
            return null;
        }
    },
    isLoggedIn() {
        return !!this.token();
    },
    setSession(token, user) {
        localStorage.setItem('auth_token', token);
        localStorage.setItem('auth_user', JSON.stringify(user));
    },
    clearSession() {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
    },
    async logout() {
        try {
            await window.api.post('/auth/logout');
        } catch {}
        this.clearSession();
        window.location.href = '/';
    },
};

window.formatPrice = (amountMinor, currency = 'USD') => {
    const amount = (Number(amountMinor) || 0) / 100;
    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: (currency || 'USD').toUpperCase(),
        }).format(amount);
    } catch {
        return `$${amount.toFixed(2)}`;
    }
};

window.formatRelativeTime = (input) => {
    if (!input) return '';
    const date = input instanceof Date ? input : new Date(input);
    if (isNaN(date.getTime())) return '';
    const diffSec = Math.round((date.getTime() - Date.now()) / 1000);
    const absSec = Math.abs(diffSec);
    const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });
    if (absSec < 60) return rtf.format(diffSec, 'second');
    if (absSec < 3600) return rtf.format(Math.round(diffSec / 60), 'minute');
    if (absSec < 86400) return rtf.format(Math.round(diffSec / 3600), 'hour');
    if (absSec < 2592000) return rtf.format(Math.round(diffSec / 86400), 'day');
    if (absSec < 31536000) return rtf.format(Math.round(diffSec / 2592000), 'month');
    return rtf.format(Math.round(diffSec / 31536000), 'year');
};

// Lazy-initialised Echo. We only spin up the WebSocket connection when a page
// actually subscribes to a channel (window.realtime.channel('...')). If Reverb
// isn't reachable, the chat page falls back to its 5s REST polling — no UX
// regression, just no live push.
window.Pusher = Pusher;

let echoInstance = null;
function makeEcho() {
    if (echoInstance) return echoInstance;
    try {
        echoInstance = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
            wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
            enabledTransports: ['ws', 'wss'],
            authorizer: (channel) => ({
                authorize: (socketId, callback) => {
                    axios
                        .post('/broadcasting/auth',
                            { socket_id: socketId, channel_name: channel.name },
                            {
                                headers: {
                                    Authorization: 'Bearer ' + (localStorage.getItem('auth_token') || ''),
                                    'X-Requested-With': 'XMLHttpRequest',
                                    Accept: 'application/json',
                                },
                            }
                        )
                        .then((res) => callback(null, res.data))
                        .catch((err) => callback(err, null));
                },
            }),
        });
    } catch (e) {
        console.warn('Echo init failed; live updates disabled.', e);
        return null;
    }
    return echoInstance;
}

window.realtime = {
    enabled() {
        return !!(import.meta.env.VITE_REVERB_APP_KEY && window.auth.isLoggedIn());
    },
    privateChannel(name) {
        const echo = makeEcho();
        return echo ? echo.private(name) : null;
    },
    leave(name) {
        if (echoInstance) {
            try { echoInstance.leave('private-' + name); } catch {}
        }
    },
};

window.conditionChipClasses = (color) => {
    const map = {
        green:   'bg-green-100 text-green-700 ring-1 ring-green-200',
        emerald: 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
        blue:    'bg-blue-100 text-blue-700 ring-1 ring-blue-200',
        amber:   'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
        orange:  'bg-orange-100 text-orange-700 ring-1 ring-orange-200',
        red:     'bg-red-100 text-red-700 ring-1 ring-red-200',
    };
    return map[color] || 'bg-gray-100 text-gray-700 ring-1 ring-gray-200';
};

window.extractApiError = (error) => {
    if (!error?.response) return 'Network error. Please try again.';
    const data = error.response.data;
    if (data?.errors && typeof data.errors === 'object') {
        return Object.values(data.errors).flat().join(' ');
    }
    return data?.message || 'Something went wrong.';
};
