{{-- Redirects unauthenticated users to /login. Include at top of protected pages. --}}
<script>
    (function () {
        if (!localStorage.getItem('auth_token')) {
            const next = encodeURIComponent(window.location.pathname + window.location.search);
            window.location.replace('/login?next=' + next);
        }
    })();
</script>
