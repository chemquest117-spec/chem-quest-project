<?php

namespace App\Notifications\Channels;

use App\Services\PushNotificationService;
use Illuminate\Notifications\Notification;

/**
 * Custom notification channel for Firebase Cloud Messaging (FCM).
 *
 * Any notification that includes 'fcm' in its via() method and implements
 * a toFcm($notifiable) method will be delivered through this channel.
 */
class FcmChannel
{
    public function __construct(
        private PushNotificationService $pushService,
    ) {}

    /**
     * Send the given notification via FCM push.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $this->pushService->isEnabled()) {
            \Illuminate\Support\Facades\Log::warning('FCM is disabled in settings.');
            return;
        }

        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        $fcmData = $notification->toFcm($notifiable);
        \Illuminate\Support\Facades\Log::info('Attempting to send FCM to user: ' . $notifiable->id);

        $title = $fcmData['title'] ?? config('app.name');
        $body = $fcmData['body'] ?? '';

        // Merge any explicit data and top-level metadata into the final payload
        $data = array_merge(
            $fcmData['data'] ?? [],
            array_diff_key($fcmData, array_flip(['title', 'body', 'data']))
        );

        if (empty($body)) {
            return;
        }

        $this->pushService->sendToUser($notifiable, $title, $body, $data);
    }
}
