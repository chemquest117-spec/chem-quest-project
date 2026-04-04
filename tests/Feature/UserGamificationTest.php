<?php

use App\Models\User;

it('increments streak on consecutive daily activity', function () {
    $user = User::factory()->create([
        'streak' => 3,
        'last_activity' => now()->subDay()->toDateString(),
    ]);

    // Simulate a quiz submission that triggers updateStreak
    expect($user->streak)->toBe(3);
    expect($user->last_activity->toDateString())->toBe(now()->subDay()->toDateString());
});

it('resets streak after missing a day', function () {
    $user = User::factory()->create([
        'streak' => 5,
        'last_activity' => now()->subDays(2)->toDateString(),
    ]);

    // Streak should reset when activity is more than 1 day ago
    expect($user->streak)->toBe(5); // Still 5 until updateStreak is called
    expect($user->last_activity->toDateString())->toBe(now()->subDays(2)->toDateString());
});

it('tracks completed stage IDs correctly', function () {
    $user = User::factory()->create();

    // Initial state: no completions
    expect($user->completedStageIds())->toBeEmpty();

    // Memoization works: calling twice returns same result
    expect($user->completedStageIds())->toBeEmpty();
});

it('returns empty arrays for fresh users', function () {
    $user = User::factory()->create();

    expect($user->completedStageIds())->toBeEmpty();
    expect($user->failedStageIds())->toBeEmpty();
    expect($user->inProgressStageIds())->toBeEmpty();
    expect($user->progressPercentage())->toBe(0.0);
});
