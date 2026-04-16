<?php

use App\Models\Question;
use Database\Seeders\QuestionSeeder;
use Database\Seeders\StageSeeder;
use Illuminate\Support\Facades\Artisan;

it('question factory produces normalized mcq data', function () {
    $question = Question::factory()->create();

    expect($question->type)->toBe('mcq');
    expect($question->correct_answer)->toBe('a');
    expect($question->option_a)->not->toBeNull();
    expect($question->option_b)->not->toBeNull();
    expect($question->option_c)->not->toBeNull();
    expect($question->option_d)->not->toBeNull();
});

it('question seeder maps question shape to supported types', function () {
    Artisan::call('db:seed', ['--class' => StageSeeder::class, '--force' => true]);
    Artisan::call('db:seed', ['--class' => QuestionSeeder::class, '--force' => true]);

    expect(Question::count())->toBeGreaterThan(0);
    expect(
        Question::query()
            ->whereNotIn('type', ['mcq', 'complete'])
            ->count()
    )->toBe(0);

    // MCQ records must preserve options and correct answer.
    $brokenMcqCount = Question::query()
        ->where('type', 'mcq')
        ->where(function ($query) {
            $query->whereNull('option_a')
                ->orWhereNull('option_b')
                ->orWhereNull('option_c')
                ->orWhereNull('option_d')
                ->orWhereNull('correct_answer');
        })
        ->count();

    expect($brokenMcqCount)->toBe(0);

    // Complete records should carry the JSON expected_answers payload.
    $brokenCompleteCount = Question::query()
        ->where('type', 'complete')
        ->whereNull('expected_answers')
        ->count();

    expect($brokenCompleteCount)->toBe(0);
});
