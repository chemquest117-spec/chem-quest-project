<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProblematicQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $enrichmentSeeder = app(AnalyticsEnrichmentSeeder::class);
        $stages = Stage::with('questions')->orderBy('order')->get();
        $students = User::student()->get();
        $problematicQuestionIds = Question::query()
            ->whereIn('difficulty', ['medium', 'hard'])
            ->inRandomOrder()
            ->take(10)
            ->pluck('id')
            ->all();

        $enrichmentSeeder->seedProblematicQuestionsData($students, $stages, $problematicQuestionIds);
    }
}
