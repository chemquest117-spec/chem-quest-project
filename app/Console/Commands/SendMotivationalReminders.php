<?php

namespace App\Console\Commands;

use App\Services\MotivationService;
use Illuminate\Console\Command;

/**
 * Scheduled command to send motivational notifications:
 * - Comeback notifications for inactive students (2+ days)
 * - Streak milestone celebrations
 *
 * Designed to run daily via Laravel Scheduler.
 */
class SendMotivationalReminders extends Command
{
    protected $signature = 'motivation:send-reminders';

    protected $description = 'Send motivational notifications: comeback alerts for inactive students and streak milestones';

    public function handle(MotivationService $motivationService): int
    {
        $this->info('Processing motivational notifications...');

        // Comeback notifications for inactive students
        $comebackCount = $motivationService->sendComebackNotifications();
        $this->info("Comeback notifications sent: {$comebackCount}");

        // Streak milestone celebrations
        $streakCount = $motivationService->sendStreakMilestoneNotifications();
        $this->info("Streak milestone notifications sent: {$streakCount}");

        $total = $comebackCount + $streakCount;
        $this->info("Total motivational notifications sent: {$total}");

        return self::SUCCESS;
    }
}
