<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Unified motivational notification supporting multiple categories:
 *
 * - success  → quiz passed
 * - failure  → quiz failed (encouraging)
 * - streak   → streak milestone reached
 * - comeback → inactive student re-engagement
 * - level_up → close to completing all stages
 * - reminder → study time reminder
 *
 * Delivers via database (in-app) + FCM push (mobile) simultaneously.
 */
class MotivationalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $category,
        public string $messageEn,
        public string $messageAr,
        public array $metadata = [],
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];

        // Add FCM push if user has device tokens registered
        if ($notifiable->deviceTokens()->exists()) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    public function toArray($notifiable): array
    {
        return array_merge([
            'message' => $this->messageEn,
            'message_en' => $this->messageEn,
            'message_ar' => $this->messageAr,
            'type' => $this->resolveType(),
            'category' => $this->category,
        ], $this->metadata);
    }

    /**
     * FCM push notification payload.
     */
    public function toFcm($notifiable): array
    {
        // Use the user's locale preference, or default to English
        $locale = $notifiable->locale ?? app()->getLocale();
        $body = $locale === 'ar' ? $this->messageAr : $this->messageEn;

        return [
            'title' => $this->resolveTitle($locale),
            'body' => $body,
            'data' => array_merge([
                'category' => $this->category,
                'type' => $this->resolveType(),
            ], $this->metadata),
        ];
    }

    /**
     * Map category to notification type for UI display.
     */
    private function resolveType(): string
    {
        return match ($this->category) {
            'success', 'streak', 'level_up' => 'success',
            'failure' => 'warning',
            'comeback' => 'info',
            'reminder' => 'info',
            default => 'info',
        };
    }

    /**
     * Resolve a human-readable push notification title.
     */
    private function resolveTitle(string $locale): string
    {
        return match ($this->category) {
            'success' => $locale === 'ar' ? '🎉 أحسنت!' : '🎉 Great Job!',
            'failure' => $locale === 'ar' ? '💪 لا تستسلم!' : '💪 Don\'t Give Up!',
            'streak' => $locale === 'ar' ? '🔥 سلسلة رائعة!' : '🔥 Streak!',
            'comeback' => $locale === 'ar' ? '👋 نفتقدك!' : '👋 We Miss You!',
            'level_up' => $locale === 'ar' ? '🚀 قريب من الإنجاز!' : '🚀 Almost There!',
            'reminder' => $locale === 'ar' ? '📚 وقت المذاكرة!' : '📚 Study Time!',
            default => config('app.name'),
        };
    }
}
