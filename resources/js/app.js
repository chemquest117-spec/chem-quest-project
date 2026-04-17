import './bootstrap';

import Alpine from 'alpinejs';
import { initFCM } from './firebase-messaging';

window.Alpine = Alpine;

Alpine.start();

// Initialize Firebase Cloud Messaging for push notifications
// Only runs on authenticated pages (where CSRF meta tag exists)
if (document.querySelector('meta[name="csrf-token"]')) {
    initFCM();
}

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
