<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StageController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $stages = Cache::remember('all_stages_with_count', 43200, function () {
                return Stage::orderBy('order')->withCount('questions')->get();
            });

            $completedIds = $user->completedStageIds();
            $failedIds = $user->failedStageIds();
            $inProgressIds = $user->inProgressStageIds();

            $metaTitle = 'Stages'.' — '.config('app.name');
            $metaDescription = 'Explore all available chemistry stages and track your progress.';

            return view('stages.index', compact('stages', 'completedIds', 'failedIds', 'inProgressIds', 'user', 'metaTitle', 'metaDescription'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while loading the stages. Please try again, or contact support if the problem persists.');
        }
    }

    public function show(Request $request, Stage $stage)
    {
        try {
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

            $metaTitle = 'Stage: '.$stage->getTranslatedTitle().' — '.config('app.name');
            $metaDescription = 'Take on the '.$stage->getTranslatedTitle().' challenge completely tailored for you.';

            return view('stages.show', compact('stage', 'isCompleted', 'bestAttempt', 'attemptHistory', 'hasActiveAttempt', 'user', 'metaTitle', 'metaDescription'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while loading the stage. Please try again, or contact support if the problem persists.');
        }
    }
}
