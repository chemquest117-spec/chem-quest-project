<?php

namespace App\Notifications;

use App\Models\StudyPlan;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class StudyReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public StudyPlan $plan,
        public int $taskCount,
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
            'message' => __('planner.reminder_message', ['count' => $this->taskCount], 'en'),
            'message_en' => __('planner.reminder_message', ['count' => $this->taskCount], 'en'),
            'message_ar' => __('planner.reminder_message', ['count' => $this->taskCount], 'ar'),
            'type' => 'info',
            'category' => 'reminder',
            'plan_id' => $this->plan->id,
            'task_count' => $this->taskCount,
        ];
    }

    public function toFcm($notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        return [
            'title' => $locale === 'ar' ? '📚 وقت المذاكرة!' : '📚 Study Time!',
            'body' => $locale === 'ar'
                ? __('planner.reminder_message', ['count' => $this->taskCount], 'ar')
                : __('planner.reminder_message', ['count' => $this->taskCount], 'en'),
            'data' => [
                'category' => 'reminder',
                'plan_id' => $this->plan->id,
            ],
        ];
    }
}
