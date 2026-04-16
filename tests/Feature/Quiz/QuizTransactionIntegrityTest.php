<?php

use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use App\Services\ProgressSyncService;

use function Pest\Laravel\actingAs;

it('rolls back grading writes when progress sync fails inside transaction', function () {
    $user = User::factory()->create();
    $stage = Stage::factory()->create([
        'order' => 1,
        'passing_percentage' => 50,
    ]);

    $question = Question::factory()->create([
        'stage_id' => $stage->id,
        'type' => 'mcq',
        'correct_answer' => 'a',
    ]);

    $attempt = StageAttempt::factory()->create([
        'user_id' => $user->id,
        'stage_id' => $stage->id,
        'completed_at' => null,
        'total_questions' => 1,
    ]);

    $answer = AttemptAnswer::create([
        'stage_attempt_id' => $attempt->id,
        'question_id' => $question->id,
    ]);

    $failingService = new class extends ProgressSyncService
    {
        public function syncFromAttempt(StageAttempt $attempt): void
        {
            throw new RuntimeException('simulated sync failure');
        }
    };

    $this->app->instance(ProgressSyncService::class, $failingService);

    actingAs($user)
        ->post(route('quiz.submit', $attempt), [
            'answers' => [$question->id => 'a'],
        ])
        ->assertSessionHas('error');

    $attempt->refresh();
    $answer->refresh();

    expect($attempt->completed_at)->toBeNull();
    expect($attempt->score)->toBe(0);
    expect($attempt->passed)->toBeFalse();
    expect($answer->selected_answer)->toBeNull();
    expect($answer->is_correct)->toBeFalse();
});
