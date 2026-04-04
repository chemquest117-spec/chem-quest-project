<?php

namespace Database\Factories;

use App\Models\StudyPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyPlan>
 */
class StudyPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'exam_date' => now()->addDays(30),
            'start_date' => now(),
            'preferred_days' => ['mon', 'wed', 'fri'],
            'hours_per_day' => 2.0,
            'pace' => StudyPlan::PACE_MEDIUM,
            'total_progress' => 0,
            'status' => StudyPlan::STATUS_ACTIVE,
        ];
    }
}
