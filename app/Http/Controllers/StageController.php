<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $stages = Cache::remember('all_stages_with_count', 43200, function () {
            return Stage::orderBy('order')->withCount('questions')->get();
        });
        
        $completedIds = $user->completedStageIds();
        $failedIds = $user->failedStageIds();
        $inProgressIds = $user->inProgressStageIds();

        return view('stages.index', compact('stages', 'completedIds', 'failedIds', 'inProgressIds', 'user'));
    }

    public function show(Request $request, Stage $stage)
    {
        $user = $request->user();
        
        $stages = Cache::remember('all_stages', 86400, function () {
            return Stage::orderBy('order')->get();
        });
        
        $completedIds = $user->completedStageIds();

        if (! $stage->isUnlockedFor($user, $stages, $completedIds)) {
            return redirect()->route('stages.index')
                ->with('error', __('messages.stage_locked'));
        }

        $isCompleted = in_array($stage->id, $completedIds);

        if ($isCompleted) {
            return redirect()->route('stages.index')
                ->with('error', 'You have already completed this stage.');
        }

        $stage->loadCount('questions');
        $bestAttempt = $stage->bestAttemptFor($user);
        $hasActiveAttempt = $stage->attempts()
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->exists();

        $attemptHistory = $stage->attempts()
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('stages.show', compact('stage', 'isCompleted', 'bestAttempt', 'attemptHistory', 'hasActiveAttempt', 'user'));
    }
}
