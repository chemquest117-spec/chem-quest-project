<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminAnnouncement extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $titleEn,
        public string $titleAr,
        public string $messageEn,
        public string $messageAr,
        public string $type = 'info' // success, info, warning
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->deviceTokens()->exists()) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    public function toArray($notifiable): array
    {
        return [
            'title_en' => $this->titleEn,
            'title_ar' => $this->titleAr,
            'message_en' => $this->messageEn,
            'message_ar' => $this->messageAr,
            'message' => $this->messageEn, // fallback for legacy views
            'type' => $this->type,
            'category' => 'announcement',
        ];
    }

    public function toFcm($notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        return [
            'title' => $locale === 'ar' ? $this->titleAr : $this->titleEn,
            'body' => $locale === 'ar' ? $this->messageAr : $this->messageEn,
            'icon' => '/favicon-32x32.png',
            'type' => $this->type,
            'category' => 'announcement', // Important for the frontend filter
            'data' => [
                'category' => 'announcement',
                'type' => $this->type,
                'click_action' => url('/'),
            ],
        ];
    }
}
