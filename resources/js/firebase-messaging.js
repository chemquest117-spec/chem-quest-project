/**
 * Firebase Cloud Messaging (FCM) — Client-Side Push Notification Integration
 *
 * This module:
 * 1. Initializes Firebase with the ChemTrack project config
 * 2. Requests notification permission from the user
 * 3. Obtains the FCM device token
 * 4. Sends the token to the Laravel backend for storage
 * 5. Handles foreground push notifications via the toast system
 *
 * The service worker (public/firebase-messaging-sw.js) handles background messages.
 */

import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';

const firebaseConfig = {
    apiKey: "AIzaSyDtxoTEV6LLbkarbDRqGGoEFb_gLHAqEGI",
    authDomain: "chem-track-58071.firebaseapp.com",
    projectId: "chem-track-58071",
    storageBucket: "chem-track-58071.firebasestorage.app",
    messagingSenderId: "297929137412",
    appId: "1:297929137412:web:358794f21e4c5549af4451",
    measurementId: "G-JYZ2WZC6SW",
};

// VAPID key for web push (generate from Firebase Console → Project Settings → Cloud Messaging → Web Push certificates)
const VAPID_KEY = import.meta.env.VITE_FIREBASE_VAPID_KEY || '';

let messaging = null;

/**
 * Initialize Firebase and FCM messaging.
 */
function initFirebase() {
    try {
        const app = initializeApp(firebaseConfig);
        messaging = getMessaging(app);
        return true;
    } catch (err) {
        console.warn('[FCM] Firebase initialization failed:', err.message);
        return false;
    }
}

/**
 * Request notification permission and register device token with the backend.
 */
async function requestNotificationPermission() {
    if (!('Notification' in window)) {
        console.warn('[FCM] Notifications not supported in this browser.');
        return null;
    }

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        console.info('[FCM] Notification permission denied by user.');
        return null;
    }

    return await registerToken();
}

/**
 * Get the FCM token and send it to the Laravel backend.
 */
async function registerToken() {
    if (!messaging) return null;

    try {
        // Register the service worker explicitly
        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');

        const token = await getToken(messaging, {
            vapidKey: VAPID_KEY,
            serviceWorkerRegistration: registration,
        });

        if (!token) {
            console.warn('[FCM] No registration token available.');
            return null;
        }

        // Send token to Laravel backend
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        await fetch('/device-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                token: token,
                platform: detectPlatform(),
            }),
        });

        console.info('[FCM] Device token registered successfully.');

        if (import.meta.env.DEV) {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: 'FCM Connection Established!' }
            }));
        }

        return token;
    } catch (err) {
        console.warn('[FCM] Token registration failed:', err.message);
        if (import.meta.env.DEV) {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'error', message: 'FCM Error: ' + err.message }
            }));
        }
        return null;
    }
}

/**
 * Listen for foreground push notifications and display them as toasts.
 */
function listenForForegroundMessages() {
    if (!messaging) return;

    onMessage(messaging, (payload) => {
        console.info('[FCM] Foreground message received:', payload);
        handleMessagePayload(payload);
    });

    // Also listen for messages sent from the Service Worker (in case it catches the message first)
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'FCM_SW_MESSAGE') {
                console.info('[FCM] Received message broadcast from Service Worker');
                handleMessagePayload(event.data.payload);
            }
        });
    }
}

/**
 * Common handler for FCM message payloads (either from onMessage or Service Worker broadcast).
 */
function handleMessagePayload(payload) {
    const notification = payload.notification || {};
    const title = notification.title || 'ChemTrack';
    const body = notification.body || '';
    const data = payload.data || {};

    const typeMap = {
        success: 'success',
        failure: 'warning',
        streak: 'success',
        comeback: 'info',
        level_up: 'success',
        reminder: 'info',
        announcement: 'info',
    };

    const toastType = typeMap[data.category] || 'info';

    // Dispatch to Alpine.js toast system
    window.dispatchEvent(new CustomEvent('toast', {
        detail: {
            type: toastType,
            message: `${title}: ${body}`,
        },
    }));

    // Dispatch to Alpine.js notification bell counter and dropdown list
    window.dispatchEvent(new CustomEvent('fcm-message', {
        detail: {
            title: title,
            body: body,
            category: data.category
        }
    }));
}

/**
 * Detect the user's platform for device token registration.
 */
function detectPlatform() {
    const ua = navigator.userAgent || '';
    if (/android/i.test(ua)) return 'android';
    if (/iPad|iPhone|iPod/.test(ua)) return 'ios';
    return 'web';
}

/**
 * Main initialization — runs when the DOM is ready on authenticated pages.
 */
export function initFCM() {
    if (!initFirebase()) return;

    // Request permission and register token
    requestNotificationPermission();

    // Listen for foreground messages
    listenForForegroundMessages();
}
