<?php

namespace App\Services;

use App\Models\StageAttempt;
use App\Models\StudyPlan;
use App\Models\WeeklyStudyPlan;
use App\Models\WeeklyStudyPlanDay;

class ProgressSyncService
{
    /**
     * Sync a completed stage attempt with the user's active study plan.
     * Automatically marks the corresponding study plan item as completed.
     */
    public function syncFromAttempt(StageAttempt $attempt): void
    {
        // Only sync passed attempts
        if (! $attempt->passed) {
            return;
        }

        $user = $attempt->user;
        $activePlan = $user->activeStudyPlan();

        if (! $activePlan) {
            return;
        }

        // Find the earliest pending item for this stage in the active plan
        $item = $activePlan->items()
            ->where('stage_id', $attempt->stage_id)
            ->pending()
            ->orderBy('scheduled_date')
            ->first();

        if ($item) {
            $item->markCompleted();
        }

        // --- Weekly Planner Sync ---
        $weeklyPlan = WeeklyStudyPlan::where('user_id', $user->id)
            ->where('stage_id', $attempt->stage_id)
            ->where('status', 'active')
            ->first();

        if ($weeklyPlan) {
            /** @var WeeklyStudyPlan $weeklyPlan */
            $testDay = $weeklyPlan->days()->where('action_type', 'test')->first();
            if ($testDay) {
                /** @var WeeklyStudyPlanDay $testDay */
                $testDay->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                ]);
            }

            // Re-evaluate weekly plan status
            $studyDay = $weeklyPlan->days()->where('action_type', 'study')->first();
            if ((! $studyDay || $studyDay->is_completed) && $testDay) {
                $weeklyPlan->update(['status' => 'completed']);
            }
        }
    }

    /**
     * Bulk-sync all completed stages for a user's active plan.
     * Useful when activating a new plan or recalculating.
     */
    public function syncAllForPlan(StudyPlan $plan): void
    {
        $user = $plan->user;
        $completedStageIds = $user->completedStageIds();

        $pendingItems = $plan->items()
            ->pending()
            ->whereIn('stage_id', $completedStageIds)
            ->get();

        foreach ($pendingItems as $item) {
            $item->markCompleted();
        }
    }

    /**
     * Check and expire overdue plans.
     */
    public function checkExpiredPlans(): int
    {
        $expiredCount = StudyPlan::active()
            ->where('exam_date', '<', now()->toDateString())
            ->update(['status' => StudyPlan::STATUS_EXPIRED]);

        return $expiredCount;
    }
}
