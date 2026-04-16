<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'stage_id' => Stage::factory(),
            'question_text' => $this->faker->sentence().'?',
            'question_text_ar' => $this->faker->sentence().'?',
            'type' => 'mcq',
            'option_a' => 'A',
            'option_b' => 'B',
            'option_c' => 'C',
            'option_d' => 'D',
            'correct_answer' => 'a',
            'difficulty' => 'easy',
            'topic' => 'General',
            'expected_answer' => 'A',
        ];
    }
}
