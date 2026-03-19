<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

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

               // Difficulty breakdown
               $difficultyStats = [
                    'easy' => StageAttempt::whereHas('answers', fn($q) => $q->whereHas('question', fn($q2) => $q2->where('difficulty', 'easy')))->count(),
                    'medium' => StageAttempt::whereHas('answers', fn($q) => $q->whereHas('question', fn($q2) => $q2->where('difficulty', 'medium')))->count(),
                    'hard' => StageAttempt::whereHas('answers', fn($q) => $q->whereHas('question', fn($q2) => $q2->where('difficulty', 'hard')))->count(),
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

               // Problematic Questions
               $problematicQuestions = \App\Models\AttemptAnswer::with('question.stage')
                    ->get()
                    ->groupBy('question_id')
                    ->map(function ($answers) {
                         $total = $answers->count();
                         if ($total < 2) return null; // Only statistically relevant data
                         
                         $correct = $answers->where('is_correct', true)->count();
                         return [
                              'question' => $answers->first()->question,
                              'total_attempts' => $total,
                              'failure_rate' => round((1 - ($correct / $total)) * 100, 1),
                         ];
                    })
                    ->filter()
                    ->sortByDesc('failure_rate')
                    ->take(5)
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
