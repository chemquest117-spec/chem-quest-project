<?php

namespace Database\Factories;

use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageFactory extends Factory
{
    protected $model = Stage::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word().' Stage',
            'title_ar' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence(),
            'order' => fake()->unique()->numberBetween(10, 1000),
            'time_limit_minutes' => 60,
            'passing_percentage' => 50,
            'points_reward' => 10,
            'marks_weight' => 20,
            'estimated_study_minutes' => 60,
            'importance_score' => 5,
            'recommended_week' => 1,
        ];
    }
}
