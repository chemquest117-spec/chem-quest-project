<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttemptAnswer;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
     public function index()
     {
          $data = Cache::remember('admin_analytics_data', 60 * 15, function () {
               // Stage performance stats
               $stageAggregates = StageAttempt::whereNotNull('completed_at')
                    ->selectRaw('stage_id, 
                         count(*) as total_attempts, 
                         sum(score) as sum_score, 
                         avg(total_questions) as avg_questions,
                         sum(case when passed = true then 1 else 0 end) as passed_count,
                         avg(time_spent_seconds) as avg_time')
                    ->groupBy('stage_id')
                    ->get()
                    ->keyBy('stage_id');

               $stages = Stage::orderBy('order')->get();
               $stageStats = [];

               foreach ($stages as $stage) {
                    $agg = $stageAggregates->get($stage->id);

                    $attemptsCount = $agg ? (int) $agg->total_attempts : 0;
                    $sumScore = $agg ? (float) $agg->sum_score : 0;
                    $avgQuestions = $agg ? (float) $agg->avg_questions : 1;
                    $passedCount = $agg ? (int) $agg->passed_count : 0;
                    $avgTime = $agg ? (float) $agg->avg_time : 0;

                    $stageStats[] = [
                         'name' => $stage->title,
                         'order' => $stage->order,
                         'attempts' => $attemptsCount,
                         'avg_score' => round($attemptsCount > 0
                              ? ($sumScore / $attemptsCount) / max(1, $avgQuestions) * 100
                              : 0, 1),
                         'pass_rate' => $attemptsCount > 0
                              ? round(($passedCount / $attemptsCount) * 100, 1)
                              : 0,
                         'avg_time' => round($avgTime),
                    ];
               }

               // Daily activity (last 30 days)
               $startDate = Carbon::now()->subDays(29)->startOfDay();

               $activityData = StageAttempt::where('created_at', '>=', $startDate)
                    ->selectRaw('DATE(created_at) as db_date, count(*) as total_attempts, sum(case when passed = true then 1 else 0 end) as passed_attempts')
                    ->groupByRaw('DATE(created_at)')
                    ->get()
                    ->keyBy('db_date');

               $dailyActivity = [];
               for ($i = 29; $i >= 0; $i--) {
                    $dateStr = Carbon::now()->subDays($i)->toDateString();
                    $activity = $activityData->get($dateStr);

                    $dailyActivity[] = [
                         'date' => Carbon::parse($dateStr)->format('M d'),
                         'attempts' => $activity ? (int) $activity->total_attempts : 0,
                         'passed' => $activity ? (int) $activity->passed_attempts : 0,
                    ];
               }

               // Difficulty breakdown — aggregated at DB level, no loading all records
               $difficultyStats = AttemptAnswer::join('questions', 'attempt_answers.question_id', '=', 'questions.id')
                    ->selectRaw("questions.difficulty, count(*) as cnt")
                    ->groupBy('questions.difficulty')
                    ->pluck('cnt', 'difficulty')
                    ->toArray();

               $difficultyStats = [
                    'easy' => $difficultyStats['easy'] ?? 0,
                    'medium' => $difficultyStats['medium'] ?? 0,
                    'hard' => $difficultyStats['hard'] ?? 0,
               ];

               // Summary stats
               $totalStudents = User::where('is_admin', false)->count();
               $totalAttempts = StageAttempt::count();
               $overallPassRate = $totalAttempts > 0
                    ? round(StageAttempt::where('passed', true)->count() / $totalAttempts * 100, 1)
                    : 0;
               $avgStudyTime = round(StageAttempt::avg('time_spent_seconds') ?? 0);

               // Top performers
               $topPerformers = User::where('is_admin', false)
                    ->orderByDesc('total_points')
                    ->take(5)
                    ->get();

               // Problematic Questions — aggregated at DB level instead of loading entire table
               $problematicQuestions = AttemptAnswer::select(
                         'question_id',
                         DB::raw('count(*) as total_attempts'),
                         DB::raw('sum(case when is_correct = false then 1 else 0 end) as wrong_count')
                    )
                    ->groupBy('question_id')
                    ->having('total_attempts', '>=', 2)
                    ->orderByDesc(DB::raw('sum(case when is_correct = false then 1 else 0 end) * 1.0 / count(*)'))
                    ->take(5)
                    ->get()
                    ->map(function ($row) {
                         $question = \App\Models\Question::with('stage')->find($row->question_id);
                         if (!$question) return null;
                         return [
                              'question' => $question,
                              'total_attempts' => (int) $row->total_attempts,
                              'failure_rate' => round(($row->wrong_count / $row->total_attempts) * 100, 1),
                         ];
                    })
                    ->filter()
                    ->values();

               return compact(
                    'stageStats',
                    'dailyActivity',
                    'difficultyStats',
                    'totalStudents',
                    'totalAttempts',
                    'overallPassRate',
                    'avgStudyTime',
                    'topPerformers',
                    'problematicQuestions'
               );
          });

          return view('admin.analytics', $data);
     }
}
