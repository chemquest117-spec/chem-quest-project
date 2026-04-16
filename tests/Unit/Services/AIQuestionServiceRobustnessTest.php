<?php

use App\Models\Stage;
use App\Services\AIQuestionService;
use Illuminate\Support\Facades\Http;

it('falls back safely when openai returns invalid json', function () {
    config()->set('services.openai.key', 'fake-key');

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => '{this-is-not-valid-json']],
            ],
        ], 200),
    ]);

    $stage = Stage::factory()->create([
        'title' => 'General Chemistry',
    ]);

    $service = new AIQuestionService;
    $created = $service->generateQuestions($stage, 3);

    expect($created)->toHaveCount(3);
    expect($stage->questions()->count())->toBe(3);
});

it('skips invalid mcq payload entries while saving openai output', function () {
    config()->set('services.openai.key', 'fake-key');

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => json_encode([
                    'questions' => [
                        [
                            'question_text' => 'Valid question?',
                            'option_a' => 'A',
                            'option_b' => 'B',
                            'option_c' => 'C',
                            'option_d' => 'D',
                            'correct_answer' => 'A',
                            'difficulty' => 'medium',
                        ],
                        [
                            'question_text' => 'Broken missing options',
                            'option_a' => 'A',
                            'correct_answer' => 'a',
                        ],
                    ],
                ])]],
            ],
        ], 200),
    ]);

    $stage = Stage::factory()->create();

    $service = new AIQuestionService;
    $created = $service->generateQuestions($stage, 2);

    expect($created)->toHaveCount(1);
    expect($created[0]->question_text)->toBe('Valid question?');
    expect($created[0]->correct_answer)->toBe('a');
});
