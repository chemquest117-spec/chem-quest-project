<?php

namespace App\Notifications;

use App\Models\StudyPlan;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MissedStudyTask extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public StudyPlan $plan,
        public int $missedCount,
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
            'message' => __('planner.missed_message', ['count' => $this->missedCount], 'en'),
            'message_en' => __('planner.missed_message', ['count' => $this->missedCount], 'en'),
            'message_ar' => __('planner.missed_message', ['count' => $this->missedCount], 'ar'),
            'type' => 'warning',
            'category' => 'reminder',
            'plan_id' => $this->plan->id,
            'missed_count' => $this->missedCount,
        ];
    }

    public function toFcm($notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        return [
            'title' => __('planner.missed_tasks_title', [], $locale),
            'body' => $locale === 'ar'
                ? __('planner.missed_message', ['count' => $this->missedCount], 'ar')
                : __('planner.missed_message', ['count' => $this->missedCount], 'en'),
            'data' => [
                'category' => 'reminder',
                'plan_id' => $this->plan->id,
            ],
        ];
    }
}
