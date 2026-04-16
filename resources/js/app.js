import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Refresh CSRF token when returning to an old tab.
(() => {
    let lastHiddenAt = null;

    const refresh = async () => {
        try {
            await fetch('/_csrf/refresh', {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
                cache: 'no-store',
            });
        } catch {
            // If refresh fails (offline, etc.), the 419 retry interceptor will handle next request.
        }
    };

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            lastHiddenAt = Date.now();
            return;
        }

        // If hidden for >= 5 minutes, proactively refresh token.
        if (lastHiddenAt && Date.now() - lastHiddenAt >= 5 * 60 * 1000) {
            refresh();
        }
        lastHiddenAt = null;
    });
})();
