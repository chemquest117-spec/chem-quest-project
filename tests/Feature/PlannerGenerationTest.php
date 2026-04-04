<?php

use App\Models\Stage;
use App\Models\StudyPlan;
use App\Models\User;
use App\Services\PlannerGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var Tests\TestCase $this */
    $this->service = app(PlannerGenerationService::class);
    $this->user = User::factory()->create();

    // Create 3 stages for testing
    Stage::factory()->create([
        'order' => 1,
        'estimated_study_minutes' => 60,
        'marks_weight' => 20,
        'importance_score' => 8,
    ]);

    Stage::factory()->create([
        'order' => 2,
        'estimated_study_minutes' => 120,
        'marks_weight' => 30,
        'importance_score' => 9,
    ]);

    Stage::factory()->create([
        'order' => 3,
        'estimated_study_minutes' => 30,
        'marks_weight' => 10,
        'importance_score' => 5,
    ]);
});

test('it generates a study plan successfully', function () {
    $preferences = [
        'start_date' => now()->toDateString(),
        'exam_date' => now()->addDays(14)->toDateString(),
        'preferred_days' => ['mon', 'wed', 'fri'],
        'hours_per_day' => 2.0,
        'pace' => StudyPlan::PACE_MEDIUM,
    ];

    $plan = $this->service->generate($this->user, $preferences);

    expect($plan)->toBeInstanceOf(StudyPlan::class)
        ->and($plan->user_id)->toBe($this->user->id)
        ->and($plan->status)->toBe(StudyPlan::STATUS_ACTIVE)
        ->and($plan->items()->count())->toBeGreaterThan(0);
});

test('it validates minimum days required', function () {
    $preferences = [
        'start_date' => now()->toDateString(),
        'exam_date' => now()->addDays(1)->toDateString(),
        'preferred_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
        'hours_per_day' => 2.0,
        'pace' => StudyPlan::PACE_MEDIUM,
    ];

    $this->service->generate($this->user, $preferences);
})->throws(InvalidArgumentException::class, 'At least 3 days are required between start and exam date.');

test('it deactivates existing active plan when generating a new one', function () {
    $oldPlan = StudyPlan::factory()->create([
        'user_id' => $this->user->id,
        'status' => StudyPlan::STATUS_ACTIVE,
    ]);

    $preferences = [
        'start_date' => now()->toDateString(),
        'exam_date' => now()->addDays(14)->toDateString(),
        'preferred_days' => ['sun', 'mon', 'tue'],
        'hours_per_day' => 2.0,
        'pace' => StudyPlan::PACE_MEDIUM,
    ];

    $newPlan = $this->service->generate($this->user, $preferences);

    expect($oldPlan->fresh()->status)->toBe(StudyPlan::STATUS_PAUSED)
        ->and($newPlan->status)->toBe(StudyPlan::STATUS_ACTIVE);
});

test('it auto-reschedules missed items', function () {
    $plan = StudyPlan::factory()->create([
        'user_id' => $this->user->id,
        'exam_date' => now()->addDays(14),
        'preferred_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
    ]);

    // Create a missed item from yesterday
    $item = $plan->items()->create([
        'stage_id' => Stage::first()->id,
        'scheduled_date' => now()->subDay(),
        'is_completed' => false,
        'estimated_minutes' => 60,
    ]);

    $count = $this->service->reschedule($plan);

    expect($count)->toBe(1)
        ->and($item->fresh()->scheduled_date->isFuture())->toBeTrue()
        ->and($item->fresh()->auto_rescheduled)->toBeTrue();
});
