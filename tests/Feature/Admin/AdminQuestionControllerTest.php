<?php

use App\Models\Question;
use App\Models\Stage;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();
    $this->stage = Stage::factory()->create(['order' => 1]);
});

it('admin can create a complete question with expected answers', function () {
    actingAs($this->admin)
        ->post(route('admin.stages.questions.store', $this->stage), [
            'type' => 'complete',
            'question_text' => 'Oxidation number is ____',
            'explanation' => 'Derived from balancing.',
            'difficulty' => 'medium',
            'expected_answers' => [
                ['value' => -2, 'tolerance' => 0],
            ],
        ])
        ->assertRedirect(route('admin.stages.questions.index', $this->stage));

    $question = Question::query()->latest('id')->first();
    expect($question)->not->toBeNull();
    expect($question->type)->toBe('complete');
    expect((float) $question->expected_answers[0]['value'])->toBe(-2.0);
});

it('admin update clears expected answers when switching to mcq', function () {
    $question = Question::factory()->create([
        'stage_id' => $this->stage->id,
        'type' => 'complete',
        'expected_answers' => [['value' => 5, 'tolerance' => 0]],
        'correct_answer' => null,
    ]);

    actingAs($this->admin)
        ->put(route('admin.stages.questions.update', [$this->stage, $question]), [
            'type' => 'mcq',
            'question_text' => 'Which option is correct?',
            'difficulty' => 'easy',
            'option_a' => 'A',
            'option_b' => 'B',
            'option_c' => 'C',
            'option_d' => 'D',
            'correct_answer' => 'a',
        ])
        ->assertRedirect(route('admin.stages.questions.index', $this->stage));

    $question->refresh();
    expect($question->type)->toBe('mcq');
    expect($question->expected_answers)->toBeNull();
    expect($question->correct_answer)->toBe('a');
});
