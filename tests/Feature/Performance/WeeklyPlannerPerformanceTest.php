<?php

use App\Models\Stage;
use App\Models\User;
use App\Models\WeeklyStudyPlan;
use App\Models\WeeklyStudyPlanDay;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

it('loads weekly planner without excessive queries', function () {
    $user = User::factory()->create();
    $stages = Stage::factory()->count(5)->create();

    foreach ($stages as $index => $stage) {
        $plan = WeeklyStudyPlan::create([
            'user_id' => $user->id,
            'stage_id' => $stage->id,
            'week_number' => $index + 1,
            'status' => 'active',
        ]);

        WeeklyStudyPlanDay::create([
            'weekly_study_plan_id' => $plan->id,
            'day_name' => 'sun',
            'action_type' => 'study',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'is_completed' => false,
            'color' => 'indigo',
        ]);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    actingAs($user)
        ->get(route('weekly-planner.index'))
        ->assertOk();

    $queryCount = count(DB::getQueryLog());
    expect($queryCount)->toBeLessThanOrEqual(25);
});
