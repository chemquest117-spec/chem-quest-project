<?php

use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\StudyPlan;
use App\Models\User;
use App\Services\ProgressSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->stage = Stage::factory()->create();

    $this->plan = StudyPlan::factory()->create([
        'user_id' => $this->user->id,
        'status' => StudyPlan::STATUS_ACTIVE,
    ]);

    $this->item = $this->plan->items()->create([
        'stage_id' => $this->stage->id,
        'scheduled_date' => now(),
        'is_completed' => false,
        'estimated_minutes' => 60,
    ]);
});

test('it syncs passed attempt and marks item completed', function () {
    $attempt = StageAttempt::create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'passed' => true,
        'score' => 10,
        'total_questions' => 10,
    ]);

    $service = app(ProgressSyncService::class);
    $service->syncFromAttempt($attempt);

    expect($this->item->fresh()->is_completed)->toBeTrue()
        ->and($this->item->fresh()->completed_at)->not->toBeNull();
});

test('it does not sync failed attempt', function () {
    $attempt = StageAttempt::create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'passed' => false,
        'score' => 2,
        'total_questions' => 10,
    ]);

    $service = app(ProgressSyncService::class);
    $service->syncFromAttempt($attempt);

    expect($this->item->fresh()->is_completed)->toBeFalse();
});

test('it expires old plans', function () {
    $oldPlan = StudyPlan::factory()->create([
        'user_id' => $this->user->id,
        'exam_date' => now()->subDay(),
        'status' => StudyPlan::STATUS_ACTIVE,
    ]);

    $service = app(ProgressSyncService::class);
    $count = $service->checkExpiredPlans();

    expect($count)->toBeGreaterThanOrEqual(1)
        ->and($oldPlan->fresh()->status)->toBe(StudyPlan::STATUS_EXPIRED)
        ->and($this->plan->fresh()->status)->toBe(StudyPlan::STATUS_ACTIVE); // Since exam_date on this might be future
});
