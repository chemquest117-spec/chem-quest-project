<?php

namespace App\Services;

use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use App\Notifications\MotivationalNotification;

/**
 * Smart motivation engine that generates contextual, dynamic notifications
 * based on student behavior patterns.
 *
 * Triggers:
 * - Post-quiz success/failure → motivational messages with dynamic data
 * - Streak milestones (3, 7, 14, 30 days) → streak celebration
 * - Inactivity (2+ days) → comeback encouragement
 * - Close to next level (e.g., 4/5 stages done) → level-up push
 */
class MotivationService
{
    /** Streak milestones that trigger special notifications */
    private const STREAK_MILESTONES = [3, 7, 14, 30, 50, 100];

    /** Days of inactivity before sending a comeback notification */
    private const INACTIVE_THRESHOLD_DAYS = 2;

    /**
     * Send post-quiz motivational notification.
     *
     * Called after grading in QuizController::gradeAttempt().
     */
    public function afterQuizCompletion(StageAttempt $attempt, User $user): void
    {
        $stage = $attempt->stage;
        $percentage = $attempt->total_questions > 0
            ? round(($attempt->score / $attempt->total_questions) * 100)
            : 0;

        if ($attempt->passed) {
            $this->sendSuccessMotivation($user, $stage, $attempt, $percentage);
        } else {
            $this->sendFailureEncouragement($user, $stage, $attempt, $percentage);
        }

        // Check for streak milestones
        $this->checkStreakMilestone($user);

        // Check if close to completing all stages
        $this->checkLevelUpProximity($user);
    }

    /**
     * Check all students for inactivity and send comeback notifications.
     *
     * Called by the scheduled command `motivation:send-reminders`.
     *
     * @return int Number of comeback notifications sent
     */
    public function sendComebackNotifications(): int
    {
        $cutoff = now()->subDays(self::INACTIVE_THRESHOLD_DAYS);
        $count = 0;

        User::student()
            ->where(function ($q) use ($cutoff) {
                $q->where('last_activity', '<', $cutoff)
                    ->orWhereNull('last_activity');
            })
            ->whereNull('deleted_at')
            ->each(function (User $user) use (&$count) {
                // Don't spam — check if we already sent a comeback notification recently
                $recentComeback = $user->notifications()
                    ->where('created_at', '>', now()->subDays(3))
                    ->where('data', 'like', '%"category":"comeback"%')
                    ->exists();

                if (! $recentComeback) {
                    $user->notify(new MotivationalNotification(
                        category: 'comeback',
                        messageEn: __('notifications.comeback_message', [
                            'name' => $user->name,
                        ], 'en'),
                        messageAr: __('notifications.comeback_message', [
                            'name' => $user->name,
                        ], 'ar'),
                        metadata: [
                            'days_inactive' => $user->last_activity
                                ? (int) $user->last_activity->diffInDays(now())
                                : null,
                        ],
                    ));
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Send streak milestone notifications for all eligible students.
     *
     * @return int Number of streak notifications sent
     */
    public function sendStreakMilestoneNotifications(): int
    {
        $count = 0;

        foreach (self::STREAK_MILESTONES as $milestone) {
            User::student()
                ->where('streak', $milestone)
                ->whereNull('deleted_at')
                ->each(function (User $user) use ($milestone, &$count) {
                    // Avoid duplicate — only send if not already sent for this milestone
                    $alreadySent = $user->notifications()
                        ->where('created_at', '>', now()->subDay())
                        ->where('data', 'like', '%"category":"streak"%')
                        ->exists();

                    if (! $alreadySent) {
                        $user->notify(new MotivationalNotification(
                            category: 'streak',
                            messageEn: __('notifications.streak_milestone', [
                                'days' => $milestone,
                            ], 'en'),
                            messageAr: __('notifications.streak_milestone', [
                                'days' => $milestone,
                            ], 'ar'),
                            metadata: [
                                'streak' => $milestone,
                            ],
                        ));
                        $count++;
                    }
                });
        }

        return $count;
    }

    // ── Private helpers ─────────────────────────────────────────────

    private function sendSuccessMotivation(User $user, Stage $stage, StageAttempt $attempt, float $percentage): void
    {
        $stageTitle = $stage->title;
        $stageTitleAr = $stage->title_ar ?: $stage->title;
        $score = $attempt->score;
        $total = $attempt->total_questions;
        $streak = $user->streak ?? 0;

        $user->notify(new MotivationalNotification(
            category: 'success',
            messageEn: __('notifications.quiz_success', [
                'stage' => $stageTitle,
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
                'streak' => $streak,
            ], 'en'),
            messageAr: __('notifications.quiz_success', [
                'stage' => $stageTitleAr,
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
                'streak' => $streak,
            ], 'ar'),
            metadata: [
                'stage_id' => $stage->id,
                'attempt_id' => $attempt->id,
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
                'streak' => $streak,
            ],
        ));
    }

    private function sendFailureEncouragement(User $user, Stage $stage, StageAttempt $attempt, float $percentage): void
    {
        $stageTitle = $stage->title;
        $stageTitleAr = $stage->title_ar ?: $stage->title;
        $passingPct = $stage->passing_percentage;

        $user->notify(new MotivationalNotification(
            category: 'failure',
            messageEn: __('notifications.quiz_failure', [
                'stage' => $stageTitle,
                'score' => $attempt->score,
                'total' => $attempt->total_questions,
                'needed' => $passingPct,
            ], 'en'),
            messageAr: __('notifications.quiz_failure', [
                'stage' => $stageTitleAr,
                'score' => $attempt->score,
                'total' => $attempt->total_questions,
                'needed' => $passingPct,
            ], 'ar'),
            metadata: [
                'stage_id' => $stage->id,
                'attempt_id' => $attempt->id,
                'score' => $attempt->score,
                'total' => $attempt->total_questions,
                'percentage' => $percentage,
            ],
        ));
    }

    private function checkStreakMilestone(User $user): void
    {
        if (in_array($user->streak, self::STREAK_MILESTONES)) {
            $user->notify(new MotivationalNotification(
                category: 'streak',
                messageEn: __('notifications.streak_milestone', [
                    'days' => $user->streak,
                ], 'en'),
                messageAr: __('notifications.streak_milestone', [
                    'days' => $user->streak,
                ], 'ar'),
                metadata: [
                    'streak' => $user->streak,
                ],
            ));
        }
    }

    private function checkLevelUpProximity(User $user): void
    {
        $totalStages = Stage::count();
        if ($totalStages === 0) {
            return;
        }

        $completed = count($user->completedStageIds());
        $remaining = $totalStages - $completed;

        // Notify when only 1 stage remaining
        if ($remaining === 1) {
            // Avoid duplicate level-up notifications
            $alreadySent = $user->notifications()
                ->where('created_at', '>', now()->subDay())
                ->where('data', 'like', '%"category":"level_up"%')
                ->exists();

            if (! $alreadySent) {
                $user->notify(new MotivationalNotification(
                    category: 'level_up',
                    messageEn: __('notifications.level_up', [
                        'completed' => $completed,
                        'total' => $totalStages,
                    ], 'en'),
                    messageAr: __('notifications.level_up', [
                        'completed' => $completed,
                        'total' => $totalStages,
                    ], 'ar'),
                    metadata: [
                        'completed' => $completed,
                        'total' => $totalStages,
                    ],
                ));
            }
        }
    }
}
