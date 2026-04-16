<?php

use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;

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

// ===================================================================
// Complete Question (Fill-in-the-Blank Numeric) Grading Tests
// ===================================================================

it('grades exact numeric complete answer as correct', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => 7.0, 'tolerance' => 0]],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    $attemptAnswer = AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => [$completeQuestion->id => '7']])
        ->assertRedirect(route('quiz.result', $attempt));

    $attemptAnswer->refresh();
    $attempt->refresh();

    expect($attemptAnswer->is_correct)->toBeTrue();
    expect($attempt->score)->toBe(1);
});

it('grades complete answer with tolerance as correct when within range', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => 3.14, 'tolerance' => 0.01]],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    $attemptAnswer = AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    // Student enters 3.14 (exact) — should pass
    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => [$completeQuestion->id => '3.14']])
        ->assertRedirect(route('quiz.result', $attempt));

    $attemptAnswer->refresh();
    expect($attemptAnswer->is_correct)->toBeTrue();
});

it('grades complete answer within tolerance boundary as correct', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => 3.14, 'tolerance' => 0.01]],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    $attemptAnswer = AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    // Student enters 3.15 (within ±0.01) — should pass
    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => [$completeQuestion->id => '3.15']])
        ->assertRedirect(route('quiz.result', $attempt));

    $attemptAnswer->refresh();
    expect($attemptAnswer->is_correct)->toBeTrue();
});

it('grades complete answer outside tolerance as incorrect', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => 3.14, 'tolerance' => 0.01]],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    $attemptAnswer = AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    // Student enters 3.20 (outside ±0.01) — should fail
    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => [$completeQuestion->id => '3.20']])
        ->assertRedirect(route('quiz.result', $attempt));

    $attemptAnswer->refresh();
    expect($attemptAnswer->is_correct)->toBeFalse();
});

it('grades wrong numeric complete answer as incorrect', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => -2.0, 'tolerance' => 0]],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    $attemptAnswer = AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => [$completeQuestion->id => '5']])
        ->assertRedirect(route('quiz.result', $attempt));

    $attemptAnswer->refresh();
    expect($attemptAnswer->is_correct)->toBeFalse();
});

it('grades negative numeric complete answer as correct', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => -2.0, 'tolerance' => 0]],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    $attemptAnswer = AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => [$completeQuestion->id => '-2']])
        ->assertRedirect(route('quiz.result', $attempt));

    $attemptAnswer->refresh();
    expect($attemptAnswer->is_correct)->toBeTrue();
});

it('grades zero complete answer correctly', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => 0.0, 'tolerance' => 0]],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    $attemptAnswer = AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => [$completeQuestion->id => '0']])
        ->assertRedirect(route('quiz.result', $attempt));

    $attemptAnswer->refresh();
    expect($attemptAnswer->is_correct)->toBeTrue();
});

it('grades decimal precision complete answer correctly', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => 0.001, 'tolerance' => 0]],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    $attemptAnswer = AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => [$completeQuestion->id => '0.001']])
        ->assertRedirect(route('quiz.result', $attempt));

    $attemptAnswer->refresh();
    expect($attemptAnswer->is_correct)->toBeTrue();
});

it('grades large numeric complete answer correctly', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => 100000.0, 'tolerance' => 0]],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    $attemptAnswer = AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => [$completeQuestion->id => '100000']])
        ->assertRedirect(route('quiz.result', $attempt));

    $attemptAnswer->refresh();
    expect($attemptAnswer->is_correct)->toBeTrue();
});

it('rejects empty complete answer as incorrect', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => 7.0, 'tolerance' => 0]],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    // Submit without an answer for the complete question
    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => []])
        ->assertRedirect(route('quiz.result', $attempt));

    $attempt->refresh();
    expect($attempt->score)->toBe(0);
});

it('handles mixed MCQ and complete questions in one quiz', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => 42.0, 'tolerance' => 0]],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 6, // 5 MCQ + 1 complete
    ]);

    // Create attempt answers for all 5 MCQ + 1 complete
    foreach ($this->questions as $question) {
        AttemptAnswer::create([
            'stage_attempt_id' => $attempt->id,
            'question_id' => $question->id,
        ]);
    }
    AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    // Answer all MCQs correctly + complete correctly
    $answers = [];
    foreach ($this->questions as $q) {
        $answers[$q->id] = 'a';
    }
    $answers[$completeQuestion->id] = '42';

    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => $answers])
        ->assertRedirect(route('quiz.result', $attempt));

    $attempt->refresh();
    expect($attempt->score)->toBe(6);
    expect($attempt->passed)->toBeTrue();
});

it('grades multi-blank questions correctly', function () {
    $completeQuestion = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [
            ['value' => -3, 'tolerance' => 0],
            ['value' => 3, 'tolerance' => 0.1],
        ],
        'correct_answer' => null,
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    $attemptAnswer = AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $completeQuestion->id,
    ]);

    // Submit valid multi-blank answers as JSON string
    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => [$completeQuestion->id => json_encode(['-3', '3.05'])]])
        ->assertRedirect(route('quiz.result', $attempt));

    $attemptAnswer->refresh();
    expect($attemptAnswer->is_correct)->toBeTrue();

    // Reset attempt
    $attempt->update(['completed_at' => null]);
    $attemptAnswer->update(['is_correct' => false, 'selected_answer' => null]);

    // Submit invalid multi-blank answers (one wrong)
    actingAs($this->user)
        ->post(route('quiz.submit', $attempt), ['answers' => [$completeQuestion->id => json_encode(['-3', '4.0'])]])
        ->assertRedirect(route('quiz.result', $attempt));

    $attemptAnswer->refresh();
    expect($attemptAnswer->is_correct)->toBeFalse();
});
