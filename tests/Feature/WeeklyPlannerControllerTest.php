<?php

use App\Models\Stage;
use App\Models\User;
use App\Models\WeeklyStudyPlan;
use App\Models\WeeklyStudyPlanDay;

use function Pest\Laravel\actingAs;

it('rejects invalid weekly planner time ranges', function () {
    $user = User::factory()->create();
    $stage = Stage::factory()->create(['order' => 1]);
    $plan = WeeklyStudyPlan::create([
        'user_id' => $user->id,
        'stage_id' => $stage->id,
        'week_number' => 1,
        'status' => 'active',
    ]);

    actingAs($user)
        ->post(route('weekly-planner.store'), [
            'plan_id' => $plan->id,
            'day_name' => 'sun',
            'action_type' => 'study',
            'start_time' => '10:00',
            'end_time' => '09:00',
        ])
        ->assertSessionHasErrors('end_time');
});

it('prevents users from editing planner events they do not own', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $stage = Stage::factory()->create(['order' => 1]);

    $plan = WeeklyStudyPlan::create([
        'user_id' => $owner->id,
        'stage_id' => $stage->id,
        'week_number' => 1,
        'status' => 'active',
    ]);

    $event = WeeklyStudyPlanDay::create([
        'weekly_study_plan_id' => $plan->id,
        'day_name' => 'mon',
        'action_type' => 'study',
        'title' => 'Owner task',
        'start_time' => '09:00',
        'end_time' => '10:00',
        'is_completed' => false,
        'color' => 'indigo',
    ]);

    actingAs($otherUser)
        ->put(route('weekly-planner.update', $event), [
            'title' => 'Hacked',
        ])
        ->assertForbidden();
});
