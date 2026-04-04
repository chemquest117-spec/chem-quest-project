<?php

use App\Models\Stage;
use App\Models\User;

it('stage 1 is always unlocked', function () {
    $user = User::factory()->create();
    $stage = Stage::factory()->create(['order' => 1]);

    expect($stage->isUnlockedFor($user))->toBeTrue();
});

it('stage 2 is locked if stage 1 is not passed', function () {
    $user = User::factory()->create();
    Stage::factory()->create(['order' => 1]);
    $stage2 = Stage::factory()->create(['order' => 2]);

    expect($stage2->isUnlockedFor($user))->toBeFalse();
});

it('stage 2 is unlocked after passing stage 1', function () {
    $user = User::factory()->create();
    $stage1 = Stage::factory()->create(['order' => 1, 'passing_percentage' => 50, 'points_reward' => 50]);
    $stage2 = Stage::factory()->create(['order' => 2]);

    // Create a passing attempt for stage 1
    $stage1->attempts()->create([
        'user_id' => $user->id,
        'score' => 5,
        'total_questions' => 5,
        'passed' => true,
        'started_at' => now(),
        'completed_at' => now(),
    ]);

    expect($stage2->isUnlockedFor($user))->toBeTrue();
});

it('uses preloaded stages and completedIds when provided', function () {
    $user = User::factory()->create();
    $stage1 = Stage::factory()->create(['order' => 1]);
    $stage2 = Stage::factory()->create(['order' => 2]);

    $allStages = Stage::orderBy('order')->get();

    // Not completed - should be locked
    expect($stage2->isUnlockedFor($user, $allStages, []))->toBeFalse();

    // Mark stage 1 as completed - should be unlocked
    expect($stage2->isUnlockedFor($user, $allStages, [$stage1->id]))->toBeTrue();
});
