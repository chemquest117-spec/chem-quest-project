<?php

use App\Models\Question;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use App\Models\AttemptAnswer;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->stage = Stage::factory()->create(['order' => 1, 'passing_percentage' => 60, 'points_reward' => 100]);
    $this->questions = Question::factory()->count(5)->create(['stage_id' => $this->stage->id, 'correct_answer' => 'a']);
});

it('grades MCQ answers correctly and awards points on pass', function () {
    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 5,
    ]);

    // Create attempt answers
    foreach ($this->questions as $question) {
        AttemptAnswer::create([
            'stage_attempt_id' => $attempt->id,
            'question_id' => $question->id,
        ]);
    }

    // Submit quiz with all correct answers (all 'a')
    $answers = [];
    foreach ($this->questions as $q) {
        $answers[$q->id] = 'a';
    }

    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => $answers])
        ->assertRedirect(route('quiz.result', $attempt));

    $attempt->refresh();
    expect($attempt->score)->toBe(5);
    expect($attempt->passed)->toBeTrue();
    expect($attempt->completed_at)->not->toBeNull();

    // Check user got points
    $this->user->refresh();
    expect($this->user->total_points)->toBeGreaterThan(0);
});

it('fails a quiz with low score', function () {
    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 5,
    ]);

    foreach ($this->questions as $question) {
        AttemptAnswer::create([
            'stage_attempt_id' => $attempt->id,
            'question_id' => $question->id,
        ]);
    }

    // Submit all wrong answers
    $answers = [];
    foreach ($this->questions as $q) {
        $answers[$q->id] = 'b'; // 'a' is correct
    }

    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => $answers])
        ->assertRedirect(route('quiz.result', $attempt));

    $attempt->refresh();
    expect($attempt->score)->toBe(0);
    expect($attempt->passed)->toBeFalse();
});

it('auto-submits expired timers on next page load', function () {
    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'started_at' => now()->subMinutes($this->stage->time_limit_minutes + 1),
    ]);

    foreach ($this->questions as $question) {
        AttemptAnswer::create([
            'stage_attempt_id' => $attempt->id,
            'question_id' => $question->id,
        ]);
    }

    // Loading the quiz page should auto-submit because time has expired
    actingAs($this->user)
        ->get(route('quiz.show', $attempt))
        ->assertRedirect(route('quiz.result', $attempt));

    $attempt->refresh();
    expect($attempt->completed_at)->not->toBeNull();
});

it('prevents viewing another student quiz', function () {
    $otherUser = User::factory()->create();
    $attempt = StageAttempt::factory()->create([
        'user_id' => $otherUser->id,
        'stage_id' => $this->stage->id,
    ]);

    actingAs($this->user)
        ->get(route('quiz.show', $attempt))
        ->assertForbidden();
});

it('prevents double submission of completed quiz', function () {
    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => now(),
        'score' => 3,
    ]);

    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => []])
        ->assertRedirect(route('quiz.result', $attempt));
});
