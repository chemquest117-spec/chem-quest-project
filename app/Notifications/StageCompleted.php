<?php

namespace App\Notifications;

use App\Models\StageAttempt;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class StageCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public StageAttempt $attempt,
        public string|array $message,
        public string $type = 'success' // success, info, warning
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
            'message' => is_string($this->message) ? $this->message : ($this->message['en'] ?? ''),
            'message_en' => is_string($this->message) ? $this->message : ($this->message['en'] ?? ''),
            'message_ar' => is_string($this->message) ? $this->message : ($this->message['ar'] ?? ''),
            'type' => $this->type,
            'category' => $this->attempt->passed ? 'success' : 'failure',
            'stage_id' => $this->attempt->stage_id,
            'stage_title' => $this->attempt->stage->title,
            'score' => $this->attempt->score,
            'total_questions' => $this->attempt->total_questions,
            'passed' => $this->attempt->passed,
        ];
    }

    public function toFcm($notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        $body = is_string($this->message)
            ? $this->message
            : ($locale === 'ar' ? ($this->message['ar'] ?? '') : ($this->message['en'] ?? ''));

        return [
            'title' => $this->attempt->passed
                ? ($locale === 'ar' ? '🎉 أحسنت!' : '🎉 Great Job!')
                : ($locale === 'ar' ? '💪 حاول مجدداً!' : '💪 Try Again!'),
            'body' => $body,
            'data' => [
                'stage_id' => $this->attempt->stage_id,
                'category' => $this->attempt->passed ? 'success' : 'failure',
            ],
        ];
    }
}
