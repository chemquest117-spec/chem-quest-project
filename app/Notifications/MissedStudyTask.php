<?php

namespace App\Notifications;

use App\Models\StudyPlan;
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
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => __('planner.missed_message', ['count' => $this->missedCount], 'en'),
            'message_en' => __('planner.missed_message', ['count' => $this->missedCount], 'en'),
            'message_ar' => __('planner.missed_message', ['count' => $this->missedCount], 'ar'),
            'type' => 'warning',
            'plan_id' => $this->plan->id,
            'missed_count' => $this->missedCount,
        ];
    }
}
