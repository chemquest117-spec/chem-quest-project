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
            'title' => $this->faker->word() . ' Stage',
            'title_ar' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence(),
            'order' => 1,
            'time_limit_minutes' => 60,
            'passing_percentage' => 50,
            'points_reward' => 10,
        ];
    }
}
