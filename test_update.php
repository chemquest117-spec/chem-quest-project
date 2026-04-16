<?php

use App\Models\Question;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Simulate AdminQuestionController@update
$question = Question::first();
$stage = $question->stage;

echo "Before update: type={$question->type}\n";

$request = Request::create('/admin/stages/'.$stage->id.'/questions/'.$question->id, 'PUT', [
    'type' => 'complete',
    'question_text' => 'The pH is ____',
    'difficulty' => 'easy',
    'expected_answers' => [
        ['value' => '7.5', 'tolerance' => '0.1'],
    ],
]);

// Actually we can't easily mock the controller request flow without setting up routes/auth.
// Let's directly call the update logic to see if there's any implicit fillable or saving issue.
$validated = [
    'type' => 'complete',
    'question_text' => 'The pH is ____',
    'difficulty' => 'easy',
    'expected_answers' => [
        ['value' => 7.5, 'tolerance' => 0.1],
    ],
];
$validated_nulls = [
    'option_a' => null, 'option_b' => null, 'option_c' => null, 'option_d' => null,
    'option_a_ar' => null, 'option_b_ar' => null, 'option_c_ar' => null, 'option_d_ar' => null,
    'correct_answer' => null,
    'expected_answer' => null, 'expected_answer_ar' => null,
];
$validated = array_merge($validated, $validated_nulls);

$question->update($validated);

$question->refresh();
echo "After update: type={$question->type}\n";
