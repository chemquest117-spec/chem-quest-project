<?php

use App\Models\StudyPlan;
use App\Models\StudyPlanItem;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

it('sends study reminders without excessive queries', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $planA = StudyPlan::factory()->create(['user_id' => $userA->id, 'status' => StudyPlan::STATUS_ACTIVE]);
    $planB = StudyPlan::factory()->create(['user_id' => $userB->id, 'status' => StudyPlan::STATUS_ACTIVE]);

    // Today: pending items (should trigger reminder)
    StudyPlanItem::factory()->count(3)->create([
        'study_plan_id' => $planA->id,
        'scheduled_date' => now()->toDateString(),
        'is_completed' => false,
    ]);

    // Yesterday: pending items (should trigger missed + reschedule path)
    StudyPlanItem::factory()->count(2)->create([
        'study_plan_id' => $planB->id,
        'scheduled_date' => now()->subDay()->toDateString(),
        'is_completed' => false,
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $exit = Artisan::call('planner:send-reminders');

    $queryCount = count(DB::getQueryLog());

    expect($exit)->toBe(0);

    // Baseline threshold; should drop after grouped aggregates.
    expect($queryCount)->toBeLessThanOrEqual(200);
});
