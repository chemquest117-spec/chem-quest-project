<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminAnalyticsController extends Controller
{
     public function index()
     {
          // Stage performance stats
          $stages = Stage::orderBy('order')->get();
          $stageStats = [];

          foreach ($stages as $stage) {
               $attempts = StageAttempt::where('stage_id', $stage->id)
                    ->whereNotNull('completed_at');

               $stageStats[] = [
                    'name' => $stage->title,
                    'order' => $stage->order,
                    'attempts' => $attempts->count(),
                    'avg_score' => round($attempts->count() > 0
                         ? ($attempts->sum('score') / $attempts->count()) / max(1, $attempts->avg('total_questions')) * 100
                         : 0, 1),
                    'pass_rate' => $attempts->count() > 0
                         ? round($attempts->clone()->where('passed', true)->count() / $attempts->count() * 100, 1)
                         : 0,
                    'avg_time' => round($attempts->avg('time_spent_seconds') ?? 0),
               ];
          }

          // Daily activity (last 30 days)
          $dailyActivity = [];
          for ($i = 29; $i >= 0; $i--) {
               $date = Carbon::now()->subDays($i)->toDateString();
               $dailyActivity[] = [
                    'date' => Carbon::parse($date)->format('M d'),
                    'attempts' => StageAttempt::whereDate('created_at', $date)->count(),
                    'passed' => StageAttempt::whereDate('created_at', $date)->where('passed', true)->count(),
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

          return view('admin.analytics', compact(
               'stageStats',
               'dailyActivity',
               'difficultyStats',
               'totalStudents',
               'totalAttempts',
               'overallPassRate',
               'avgStudyTime',
               'topPerformers'
          ));
     }
}
