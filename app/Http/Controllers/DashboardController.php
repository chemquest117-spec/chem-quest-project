<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
     public function index(Request $request)
     {
          $user = $request->user();
          $stages = Stage::orderBy('order')->get();
          
          // Single query for all completed stage IDs (prevents calling completedStageIds() multiple times)
          $completedIds = $user->attempts()
               ->where('passed', true)
               ->pluck('stage_id')
               ->unique()
               ->toArray();
          
          $completedCount = count($completedIds);
          $totalStageCount = $stages->count();

          // Determine current stage without an extra query
          $currentStage = $stages->first(fn($stage) => !in_array($stage->id, $completedIds));

          // Eager load recent attempts with stage relation
          $notifications = $user->unreadNotifications()->latest()->take(10)->get();
          $recentAttempts = $user->attempts()->with('stage')->latest()->take(5)->get();

          // Analytics metrics — single batch of queries
          $totalAttempts = $user->attempts()->count();
          $passedAttempts = $user->attempts()->where('passed', true)->count();
          $successRate = $totalAttempts > 0
               ? round($passedAttempts / $totalAttempts * 100)
               : 0;
          $avgScore = $user->attempts()->whereNotNull('completed_at')->avg('score') ?? 0;
          $totalTimeSpent = $user->attempts()->sum('time_spent_seconds');

          // Progress percentage calculated from cached values
          $progressPercentage = $totalStageCount > 0
               ? round(($completedCount / $totalStageCount) * 100, 1)
               : 0;

          return view('dashboard', compact(
               'user',
               'stages',
               'completedIds',
               'currentStage',
               'notifications',
               'recentAttempts',
               'totalAttempts',
               'completedCount',
               'successRate',
               'avgScore',
               'totalTimeSpent',
               'progressPercentage'
          ));
     }
}
