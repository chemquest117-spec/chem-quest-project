<?php

namespace Database\Factories;

use App\Models\StageAttempt;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageAttemptFactory extends Factory
{
    protected $model = StageAttempt::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'stage_id' => Stage::factory(),
            'score' => 0,
            'total_questions' => 5,
            'passed' => false,
            'time_spent_seconds' => 0,
            'started_at' => now(),
            'completed_at' => null,
        ];
    }
}
