import axios from 'axios';

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

window.extractApiError = (error) => {
    if (!error?.response) return 'Network error. Please try again.';
    const data = error.response.data;
    if (data?.errors && typeof data.errors === 'object') {
        return Object.values(data.errors).flat().join(' ');
    }
    return data?.message || 'Something went wrong.';
};
