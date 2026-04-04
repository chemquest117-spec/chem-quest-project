<?php

namespace Database\Factories;

use App\Models\Stage;
use App\Models\StudyPlan;
use App\Models\StudyPlanItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyPlanItem>
 */
class StudyPlanItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_plan_id' => StudyPlan::factory(),
            'stage_id' => Stage::factory(),
            'scheduled_date' => now(),
            'estimated_minutes' => 60,
            'marks_weight' => 10,
            'sort_order' => current([1]), // this will be overridden
            'is_completed' => false,
            'auto_rescheduled' => false,
        ];
    }
}
