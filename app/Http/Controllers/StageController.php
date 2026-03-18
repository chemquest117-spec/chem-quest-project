<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\Request;

class StageController extends Controller
{
     public function index(Request $request)
     {
          $user = $request->user();
          $stages = Stage::orderBy('order')->withCount('questions')->get();
          $completedIds = $user->completedStageIds();

          return view('stages.index', compact('stages', 'completedIds', 'user'));
     }

     public function show(Request $request, Stage $stage)
     {
          $user = $request->user();

          if (!$stage->isUnlockedFor($user)) {
               return redirect()->route('stages.index')
                    ->with('error', __('messages.stage_locked'));
          }

          $stage->loadCount('questions');
          $isCompleted = $stage->isCompletedBy($user);
          $bestAttempt = $stage->bestAttemptFor($user);
          $attemptHistory = $stage->attempts()
               ->where('user_id', $user->id)
               ->latest()
               ->take(5)
               ->get();

          return view('stages.show', compact('stage', 'isCompleted', 'bestAttempt', 'attemptHistory', 'user'));
     }
}
