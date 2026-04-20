<?php

namespace Database\Seeders;

use App\Models\Stage;
use App\Models\User;
use Illuminate\Database\Seeder;

class StagePassSeeder extends Seeder
{
    public function run(): void
    {
        $enrichmentSeeder = app(AnalyticsEnrichmentSeeder::class);
        $stages = Stage::with('questions')->orderBy('order')->get();
        $students = User::student()->get();

        $enrichmentSeeder->seedStagePassData($students, $stages);
    }
}
