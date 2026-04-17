<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;

/**
 * Sends push notifications via Firebase Cloud Messaging (FCM) HTTP v1 API.
 */
class PushNotificationService
{
    private ?string $projectId = null;

    private ?string $credentialsFile = null;

    public function __construct()
    {
        $this->credentialsFile = config('services.fcm.credentials_path') ?: storage_path('app/chem-track-58071-firebase-adminsdk-fbsvc-eca63e377e.json');

        // Allow overriding via Base64 string in .env (useful for server deployment)
        if (config('services.fcm.credentials_base64')) {
            $tempPath = storage_path('app/fcm-credentials.json');
            if (! file_exists($tempPath) || config('app.env') === 'local') {
                file_put_contents($tempPath, base64_decode(config('services.fcm.credentials_base64')));
            }
            $this->credentialsFile = $tempPath;
        }

        if (file_exists($this->credentialsFile)) {
            $json = json_decode(file_get_contents($this->credentialsFile), true);
            $this->projectId = $json['project_id'] ?? null;
        }
    }

    /**
     * Send a push notification to all devices for a given user.
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
     * Send a push notification to a single device token using HTTP v1 API.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            if (! $accessToken || ! $this->projectId) {
                Log::warning('FCM v1 missing token or project ID.');

                return false;
            }

            // FCM HTTP v1 requires all data payload values to be strings
            $stringData = [];
            foreach ($data as $key => $value) {
                $stringData[$key] = is_array($value) ? json_encode($value) : (string) $value;
            }
            $stringData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $stringData,
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high',
                        ],
                        'notification' => [
                            'body' => $body,
                            'title' => $title,
                            'icon' => '/favicon-32x32.png',
                            'requireInteraction' => true,
                        ],
                    ],
                    'android' => [
                        'notification' => [
                            'sound' => 'default',
                            'icon' => 'ic_notification',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                            ],
                        ],
                    ],
                ],
            ];

            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $response = Http::withOptions([
                'verify' => app()->isProduction() ? true : false,
            ])->withToken($accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                return true;
            }

            // Handle invalid token (unregistered device)
            if ($response->status() === 400 || $response->status() === 404) {
                $result = $response->json();
                $errorCode = $result['error']['details'][0]['errorCode'] ?? '';
                if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                    $this->handleFailedToken($token, $errorCode);
                } else {
                    Log::warning('push_notification_failed_400', ['response' => $result]);
                }
            } else {
                Log::warning('push_notification_failed_status', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
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
     * Check if FCM push notifications are enabled and configured properly.
     */
    public function isEnabled(): bool
    {
        return config('services.fcm.enabled', false)
            && file_exists($this->credentialsFile)
            && $this->projectId !== null;
    }

    /**
     * Get or generate OAuth2 access token for FCM v1 API and cache it.
     */
    private function getAccessToken(): ?string
    {
        return Cache::remember('fcm_v1_access_token', now()->addMinutes(50), function () {
            if (! file_exists($this->credentialsFile)) {
                return null;
            }

            try {
                $credentials = new ServiceAccountCredentials(
                    ['https://www.googleapis.com/auth/firebase.messaging'],
                    $this->credentialsFile
                );

                // Use a custom fetcher that uses Laravel Http client for better SSL control
                $token = $credentials->fetchAuthToken(function (RequestInterface $request, array $options = []) {
                    $response = Http::withOptions([
                        'verify' => app()->isProduction() ? true : false,
                    ])->withHeaders($request->getHeaders())
                        ->send($request->getMethod(), (string) $request->getUri(), ['body' => (string) $request->getBody()]);

                    return $response->toPsrResponse();
                });

                return $token['access_token'] ?? null;
            } catch (\Throwable $e) {
                Log::error('FCM v1 token fetch failed: '.$e->getMessage());

                return null;
            }
        });
    }

    /**
     * Clean up invalid/unregistered device tokens.
     */
    private function handleFailedToken(string $token, string $reason): void
    {
        DeviceToken::where('token', $token)->delete();
        Log::info('push_token_removed', ['token' => substr($token, 0, 20).'...', 'reason' => $reason]);
    }
}
