<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Support\CacheTTL;
use App\Support\StageSchemaCache;
use App\Support\TwoLayerCache;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StageController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $stageSchemaVersion = StageSchemaCache::version();
            $stages = TwoLayerCache::remember(
                "all_stages_with_count:v{$stageSchemaVersion}",
                CacheTTL::SEMI_REDIS,
                CacheTTL::SEMI_MEMORY,
                fn () => Stage::orderBy('order')->withCount('questions')->get(),
                CacheTTL::SEMI_STALE,
            );

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
                ->with('error', __('stages.we_encountered_an_unexpected_d6a1'));
        }
    }

    public function show(Request $request, Stage $stage)
    {
        try {
            $user = $request->user();

            $stageSchemaVersion = StageSchemaCache::version();
            $stages = TwoLayerCache::remember(
                "all_stages:v{$stageSchemaVersion}",
                CacheTTL::STATIC_REDIS,
                CacheTTL::STATIC_MEMORY,
                fn () => Stage::orderBy('order')->get(),
                CacheTTL::STATIC_STALE,
            );

            $completedIds = $user->completedStageIds();

            if (! $stage->isUnlockedFor($user, $stages, $completedIds)) {
                return redirect()->route('stages.index')
                    ->with('error', __('messages.stage_locked'));
            }

            $isCompleted = in_array($stage->id, $completedIds);

            if ($isCompleted) {
                return redirect()->route('stages.index')
                    ->with('error', __('stages.you_have_already_completed_22e1'));
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
                ->with('error', __('stages.we_encountered_an_unexpected_1349'));
        }
    }
}
