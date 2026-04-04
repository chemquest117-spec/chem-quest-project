<?php

namespace App\Notifications;

use App\Models\StudyPlan;
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
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => __('planner.reminder_message', ['count' => $this->taskCount], 'en'),
            'message_en' => __('planner.reminder_message', ['count' => $this->taskCount], 'en'),
            'message_ar' => __('planner.reminder_message', ['count' => $this->taskCount], 'ar'),
            'type' => 'info',
            'plan_id' => $this->plan->id,
            'task_count' => $this->taskCount,
        ];
    }
}
