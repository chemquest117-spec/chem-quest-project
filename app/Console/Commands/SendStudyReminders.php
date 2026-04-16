<?php

namespace App\Console\Commands;

use App\Models\StudyPlan;
use App\Models\StudyPlanItem;
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
            ->with('user')
            ->get();

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $countsByPlanId = StudyPlanItem::query()
            ->select('study_plan_id')
            ->selectRaw(
                'sum(case when scheduled_date = ? and is_completed = false then 1 else 0 end) as today_pending',
                [$today]
            )
            ->selectRaw(
                'sum(case when scheduled_date = ? and is_completed = false then 1 else 0 end) as yesterday_pending',
                [$yesterday]
            )
            ->whereIn('study_plan_id', $activePlans->pluck('id'))
            ->groupBy('study_plan_id')
            ->get()
            ->keyBy('study_plan_id');

        $remindersSent = 0;
        $missedAlerts = 0;
        $rescheduled = 0;

        foreach ($activePlans as $plan) {
            /** @var StudyPlan $plan */
            $user = $plan->user;

            // Send reminders for today's tasks
            $planCounts = $countsByPlanId->get($plan->id);
            $todayCount = (int) ($planCounts->today_pending ?? 0);

            if ($todayCount > 0) {
                $user->notify(new StudyReminder($plan, $todayCount));
                $remindersSent++;
            }

            // Check for yesterday's missed tasks
            $yesterdayMissed = (int) ($planCounts->yesterday_pending ?? 0);

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
