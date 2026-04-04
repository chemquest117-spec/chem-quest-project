<?php

namespace App\Console\Commands;

use App\Models\StudyPlan;
use App\Notifications\MissedStudyTask;
use App\Notifications\StudyReminder;
use App\Services\PlannerGenerationService;
use App\Services\ProgressSyncService;
use Illuminate\Console\Command;

class SendStudyReminders extends Command
{
    protected $signature = 'planner:send-reminders';

    protected $description = 'Send daily study reminders and missed task alerts for active study plans';

    public function handle(PlannerGenerationService $generationService, ProgressSyncService $progressService): int
    {
        $this->info('Processing study plan reminders...');

        // Expire overdue plans
        $expiredCount = $progressService->checkExpiredPlans();
        if ($expiredCount > 0) {
            $this->info("Expired {$expiredCount} overdue plan(s).");
        }

        $activePlans = StudyPlan::active()
            ->with(['user', 'items'])
            ->get();

        $remindersSent = 0;
        $missedAlerts = 0;
        $rescheduled = 0;

        foreach ($activePlans as $plan) {
            $user = $plan->user;

            // Send reminders for today's tasks
            $todayCount = $plan->items()
                ->where('scheduled_date', now()->toDateString())
                ->where('is_completed', false)
                ->count();

            if ($todayCount > 0) {
                $user->notify(new StudyReminder($plan, $todayCount));
                $remindersSent++;
            }

            // Check for yesterday's missed tasks
            $yesterdayMissed = $plan->items()
                ->where('scheduled_date', now()->subDay()->toDateString())
                ->where('is_completed', false)
                ->count();

            if ($yesterdayMissed > 0) {
                $user->notify(new MissedStudyTask($plan, $yesterdayMissed));
                $missedAlerts++;

                // Auto-reschedule missed items
                $count = $generationService->reschedule($plan);
                $rescheduled += $count;
            }
        }

        $this->info("Reminders sent: {$remindersSent}");
        $this->info("Missed alerts: {$missedAlerts}");
        $this->info("Items rescheduled: {$rescheduled}");

        return self::SUCCESS;
    }
}
