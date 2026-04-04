<?php

namespace App\Notifications;

use App\Models\StageAttempt;
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
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => is_string($this->message) ? $this->message : ($this->message['en'] ?? ''),
            'message_en' => is_string($this->message) ? $this->message : ($this->message['en'] ?? ''),
            'message_ar' => is_string($this->message) ? $this->message : ($this->message['ar'] ?? ''),
            'type' => $this->type,
            'stage_id' => $this->attempt->stage_id,
            'stage_title' => $this->attempt->stage->title,
            'score' => $this->attempt->score,
            'total_questions' => $this->attempt->total_questions,
            'passed' => $this->attempt->passed,
        ];
    }
}
