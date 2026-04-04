<?php

use App\Models\User;
use App\Models\Stage;
use App\Models\Question;
use App\Models\StageAttempt;
use function Pest\Laravel\{actingAs, postJson, get};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->stage = Stage::factory()->create();
    $this->questions = Question::factory()->count(5)->create(['stage_id' => $this->stage->id]);
});

it('allows student to start a quiz', function () {
    actingAs($this->user)
        ->post(route('quiz.start', $this->stage))
        ->assertRedirect();
        
    $this->assertDatabaseHas('stage_attempts', [
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
    ]);
});

it('prevents concurrent quiz attempts for the same stage', function () {
    // Manually create an ongoing attempt
    StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
    ]);

    actingAs($this->user)
        ->post(route('quiz.start', $this->stage))
        ->assertRedirect(route('quiz.show', 1));
});

it('auto-saves interim answers correctly', function () {
    $attempt = StageAttempt::factory()->create([
        'user_id' => $this->user->id,
        'stage_id' => $this->stage->id,
        'completed_at' => null,
    ]);
    
    $question = $this->questions->first();

    \App\Models\AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $question->id
    ]);

    actingAs($this->user)
        ->postJson(route('quiz.saveAnswer', $attempt), [
            'question_id' => $question->id,
            'answer' => 'Test Answer'
        ])
        ->assertOk();

    $this->assertDatabaseHas('attempt_answers', [
        'stage_attempt_id' => $attempt->id,
        'question_id' => $question->id,
    ]);
});
