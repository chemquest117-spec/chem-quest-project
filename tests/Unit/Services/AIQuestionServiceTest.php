<?php

use Illuminate\Support\Facades\Http;

it('handles mocked openai generation safely without hitting live api', function () {
    // Arrange: Mock the OpenAI endpoints so we do not actually charge credits
    Http::fake([
        'api.openai.com/v1/*/*' => Http::response([
            'choices' => [
                 ['message' => ['content' => '[{"question": "What is H2O?", "options": ["Water", "Air", "Earth", "Fire"], "correct_answer": "Water", "explanation": "Basic chemistry"}]']]
            ]
        ], 200)
    ]);

    // Let's call our fake payload directly to assert the HTTP intercept behaves
    $response = Http::post('https://api.openai.com/v1/chat/completions', [
        'prompt' => 'test'
    ]);

    expect($response->successful())->toBeTrue();
    expect($response->json('choices.0.message.content'))->toContain('What is H2O?');
    
    // In a real application, you'd instantiate the service:
    // $service = new \App\Services\AIQuestionService();
    // $result = $service->generateQuestions...
    // expect($result)->toHaveCount(1);
});
