import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfMeta = () => document.querySelector('meta[name="csrf-token"]');

const setCsrfToken = (token) => {
    const meta = csrfMeta();
    if (meta) meta.setAttribute('content', token);
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
};

const getCsrfToken = () => csrfMeta()?.getAttribute('content');

const refreshCsrfToken = async () => {
    const res = await fetch('/_csrf/refresh', {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
        cache: 'no-store',
    });
    if (!res.ok) {
        throw new Error(`CSRF refresh failed (${res.status})`);
    }
    const json = await res.json();
    if (json?.token) {
        setCsrfToken(json.token);
        return json.token;
    }
    throw new Error('CSRF refresh returned no token');
};

// Ensure Axios always carries the current CSRF token.
const initialToken = getCsrfToken();
if (initialToken) {
    setCsrfToken(initialToken);
}

// Retry-once strategy for 419 (CSRF token mismatch / session rotated).
window.axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const status = error?.response?.status;
        const config = error?.config;

        if (status === 419 && config && !config.__retriedAfterCsrfRefresh) {
            config.__retriedAfterCsrfRefresh = true;
            await refreshCsrfToken();
            return window.axios(config);
        }

        return Promise.reject(error);
    }
);

// Patch fetch() to refresh CSRF and retry once on 419 for same-origin requests.
const originalFetch = window.fetch.bind(window);
window.fetch = async (input, init = {}) => {
    const response = await originalFetch(input, init);
    if (response.status !== 419) {
        return response;
    }

    // Avoid retry loops.
    if (init && init.__retriedAfterCsrfRefresh) {
        return response;
    }

    try {
        await refreshCsrfToken();
    } catch {
        // If refresh fails, bubble the original 419 so the app can redirect/reload.
        return response;
    }

    const nextInit = { ...init, __retriedAfterCsrfRefresh: true };

    // Update header for retried request if present.
    const token = getCsrfToken();
    if (token) {
        const headers = new Headers(nextInit.headers || {});
        headers.set('X-CSRF-TOKEN', token);
        nextInit.headers = headers;
    }

    return originalFetch(input, nextInit);
};
