<?php

namespace App\Services;

use App\Models\StageAttempt;
use App\Models\StudyPlan;

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
            ->where('is_completed', false)
            ->orderBy('scheduled_date')
            ->first();

        if (! $item) {
            return;
        }

        $item->markCompleted();
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
            ->where('is_completed', false)
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
