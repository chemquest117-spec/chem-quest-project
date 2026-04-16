<?php

use App\Models\Question;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

it('starts a quiz attempt without excessive queries', function () {
    $user = User::factory()->create();
    $stage = Stage::factory()->create(['order' => 1]);
    Question::factory()->count(20)->create(['stage_id' => $stage->id]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $start = microtime(true);

    actingAs($user)
        ->post(route('quiz.start', $stage))
        ->assertRedirect();

    $elapsedMs = (microtime(true) - $start) * 1000;
    $queryCount = count(DB::getQueryLog());

    // Baseline threshold; should drop significantly after batching inserts.
    expect($queryCount)->toBeLessThanOrEqual(80);
    expect($elapsedMs)->toBeLessThan(3000);
});
