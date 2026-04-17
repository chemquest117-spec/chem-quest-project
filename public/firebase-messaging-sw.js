/**
 * Firebase Cloud Messaging — Service Worker for Background Push Notifications
 *
 * This file MUST live at the root of the public directory (/firebase-messaging-sw.js)
 * so that Firebase can register it with the correct scope.
 *
 * It handles push notifications received when the app tab is:
 * - In the background
 * - Closed entirely
 *
 * Foreground messages are handled by resources/js/firebase-messaging.js instead.
 */

/* eslint-disable no-undef */
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyDtxoTEV6LLbkarbDRqGGoEFb_gLHAqEGI",
    authDomain: "chem-track-58071.firebaseapp.com",
    projectId: "chem-track-58071",
    storageBucket: "chem-track-58071.firebasestorage.app",
    messagingSenderId: "297929137412",
    appId: "1:297929137412:web:358794f21e4c5549af4451",
    measurementId: "G-JYZ2WZC6SW",
});

const messaging = firebase.messaging();

/**
 * Force the new service worker to become active immediately.
 */
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(clients.claim()));

/**
 * Handle background messages — customize the notification shown to the user.
 */
messaging.onBackgroundMessage((payload) => {
    console.log('[FCM SW] Background message received:', payload);

    const notification = payload.notification || {};
    const data = payload.data || {};

    const title = notification.title || 'ChemTrack';
    const options = {
        body: notification.body || '',
        icon: '/favicon-32x32.png',
        badge: '/favicon-16x16.png',
        tag: data.category || 'general',
        data: {
            url: self.location.origin + '/dashboard',
            ...data,
        },
        // Vibration pattern: short-long-short
        vibrate: [100, 200, 100],
        // Auto-close after 10 seconds
        requireInteraction: false,
    };

    // Broadcast to tabs with retries to handle page reloads/redirects
    let attempts = 0;
    const maxAttempts = 5;
    const broadcast = () => {
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            if (clients && clients.length > 0) {
                clients.forEach((client) => {
                    client.postMessage({
                        type: 'FCM_SW_MESSAGE',
                        payload: payload
                    });
                });
            } else if (attempts < maxAttempts) {
                attempts++;
                setTimeout(broadcast, 1000); // Retry every second
            }
        });
    };
    broadcast();

    return self.registration.showNotification(title, options);
});

/**
 * Handle notification click — navigate to the appropriate page.
 */
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data?.url || '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Focus existing tab if open
            for (const client of clientList) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    return client.focus();
                }
            }
            // Otherwise open a new tab
            return clients.openWindow(url);
        })
    );
});
