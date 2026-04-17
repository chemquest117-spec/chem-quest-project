<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Support\CacheTTL;
use App\Support\MemoryCache;
use App\Support\StageSchemaCache;
use App\Support\TwoLayerCache;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Cache immutable stage schema for 24 hours
            $stageSchemaVersion = StageSchemaCache::version();
            $stages = TwoLayerCache::remember(
                "all_stages:v{$stageSchemaVersion}",
                CacheTTL::STATIC_REDIS,
                CacheTTL::STATIC_MEMORY,
                fn () => Stage::orderBy('order')->get(),
                CacheTTL::STATIC_STALE,
            );

            // Single query for all completed stage IDs
            $completedIds = $user->completedStageIds();

            $completedCount = count($completedIds);
            $totalStageCount = $stages->count();

            // Determine current stage without an extra query
            $currentStage = $stages->first(fn ($stage) => ! in_array($stage->id, $completedIds));

            // Eager load recent attempts with stage relation
            $notifications = $user->unreadNotifications()->latest()->take(10)->get();

            // Cache dashboard metrics per user for 30 minutes to save DB/Redis queries
            // Per-user data: keep it memory-only to avoid Redis command burn on Render free tier.
            $recentAttempts = MemoryCache::remember("user_{$user->id}_recent_attempts", CacheTTL::USER_MEMORY, function () use ($user) {
                return $user->attempts()->with('stage')->latest()->take(5)->get();
            });

            // Analytics metrics — single aggregate query cached heavily
            $stats = MemoryCache::remember("user_{$user->id}_dashboard_stats", CacheTTL::USER_MEMORY, function () use ($user) {
                return $user->attempts()
                    ->whereNotNull('completed_at')
                    ->selectRaw('count(*) as total_attempts, sum(case when passed = true then 1 else 0 end) as passed_attempts, avg(score) as avg_score, sum(time_spent_seconds) as total_time')
                    ->first();
            });

            $totalAttempts = (int) ($stats->total_attempts ?? 0);
            $passedAttempts = (int) ($stats->passed_attempts ?? 0);
            $successRate = $totalAttempts > 0
                ? round($passedAttempts / $totalAttempts * 100)
                : 0;
            $avgScore = round($stats->avg_score ?? 0, 1);
            $totalTimeSpent = (int) ($stats->total_time ?? 0);

            // Progress percentage calculated from cached values
            $progressPercentage = $totalStageCount > 0
                ? round(($completedCount / $totalStageCount) * 100, 1)
                : 0;

            // Weekly Planner Integration
            $currentWeek = min($completedCount + 1, 5);
            $weeklyPlan = $user->weeklyStudyPlans()
                ->where('week_number', $currentWeek)
                ->with('days')
                ->first();

            return view('dashboard', compact(
                'user',
                'stages',
                'completedIds',
                'currentStage',
                'notifications',
                'recentAttempts',
                'totalAttempts',
                'completedCount',
                'passedAttempts',
                'successRate',
                'avgScore',
                'totalTimeSpent',
                'progressPercentage',
                'weeklyPlan',
                'currentWeek'
            ));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', __('dashboard.we_encountered_an_unexpected_4e4b'));
        }
    }
}
