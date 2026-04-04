<?php

use App\Models\User;
use App\Models\Stage;
use App\Models\StageAttempt;
use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->stage = Stage::factory()->create();
});

it('calculates leaderboard correctly', function () {
    $topUser = User::factory()->create(['total_points' => 1000]);
    $lowUser = User::factory()->create(['total_points' => 10]);

    actingAs($this->user)
        ->get(route('leaderboard'))
        ->assertOk()
        ->assertSee($topUser->name);
});

it('student can view their progress on dashboard', function () {
    // Generate an attempt to reflect some progress
    StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'passed' => true,
        'score' => 100,
        'total_questions' => 10
    ]);

    actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk();
});
