<?php

use App\Models\Question;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

it('submits a quiz attempt without excessive queries', function () {
    $user = User::factory()->create();
    $stage = Stage::factory()->create(['order' => 1, 'passing_percentage' => 50]);
    Question::factory()->count(20)->create(['stage_id' => $stage->id]);

    // Create attempt + answer rows via the real start endpoint.
    actingAs($user)->post(route('quiz.start', $stage))->assertRedirect();
    $attempt = StageAttempt::query()->where('user_id', $user->id)->where('stage_id', $stage->id)->latest('id')->firstOrFail();

    DB::flushQueryLog();
    DB::enableQueryLog();

    $start = microtime(true);

    actingAs($user)
        ->post(route('quiz.submit', $attempt), [
            // Intentionally empty: forces grading to read existing answers and write results.
            'answers' => [],
        ])
        ->assertRedirect();

    $elapsedMs = (microtime(true) - $start) * 1000;
    $queryCount = count(DB::getQueryLog());

    // Baseline threshold; should drop after bulk updates.
    expect($queryCount)->toBeLessThanOrEqual(140);
    expect($elapsedMs)->toBeLessThan(5000);
});
