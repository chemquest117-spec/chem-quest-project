<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends push notifications via Firebase Cloud Messaging (FCM) HTTP v1 API.
 *
 * Configuration via .env:
 *   FCM_SERVER_KEY   — Firebase Cloud Messaging server key (legacy or v1 token)
 *   FCM_ENABLED      — Set to "true" to enable push (default: false)
 *
 * Falls back silently when FCM is disabled or misconfigured.
 */
class PushNotificationService
{
    private const FCM_URL = 'https://fcm.googleapis.com/fcm/send';

    /**
     * Send a push notification to all devices for a given user.
     *
     * @param  User  $user  Target user
     * @param  string  $title  Notification title
     * @param  string  $body  Notification body message
     * @param  array  $data  Optional data payload
     * @return int Number of devices notified
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        if (! $this->isEnabled()) {
            return 0;
        }

        $tokens = $user->deviceTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            return 0;
        }

        $sent = 0;
        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $sent++;
            }
        }

        Log::debug('push_notification_sent', [
            'user_id' => $user->id,
            'devices' => $sent,
            'title' => $title,
        ]);

        return $sent;
    }

    /**
     * Send a push notification to a single device token.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $payload = [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'icon' => 'ic_notification',
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]),
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key='.config('services.fcm.server_key'),
                'Content-Type' => 'application/json',
            ])->post(self::FCM_URL, $payload);

            if ($response->successful()) {
                return true;
            }

            // Handle invalid token (unregistered device)
            if ($response->status() === 200) {
                $result = $response->json();
                if (isset($result['failure']) && $result['failure'] > 0) {
                    $this->handleFailedToken($token, $result);
                }
            }

            return false;
        } catch (\Throwable $e) {
            Log::warning('push_notification_failed', [
                'token' => substr($token, 0, 20).'...',
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if FCM push notifications are enabled.
     */
    public function isEnabled(): bool
    {
        return config('services.fcm.enabled', false)
            && ! empty(config('services.fcm.server_key'));
    }

    /**
     * Clean up invalid/unregistered device tokens.
     */
    private function handleFailedToken(string $token, array $result): void
    {
        $results = $result['results'] ?? [];
        foreach ($results as $r) {
            $error = $r['error'] ?? '';
            if (in_array($error, ['NotRegistered', 'InvalidRegistration'])) {
                DeviceToken::where('token', $token)->delete();
                Log::info('push_token_removed', ['token' => substr($token, 0, 20).'...', 'reason' => $error]);
            }
        }
    }
}
