{{-- Client-side admin role guard. Redirects to /login if no token, or to /
     if the user is logged in but not an admin. --}}
<div x-data x-init="
    (() => {
        const u = window.auth.user();
        if (!window.auth.isLoggedIn()) {
            const next = encodeURIComponent(window.location.pathname + window.location.search);
            window.location.href = '/login?next=' + next;
            return;
        }
        if (u && u.role !== 'admin') {
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Admins only.' } }));
            setTimeout(() => { window.location.href = '/'; }, 800);
        }
    })()
"></div>
