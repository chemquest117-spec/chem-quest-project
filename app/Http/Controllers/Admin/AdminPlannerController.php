<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Support\StageSchemaCache;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminPlannerController extends Controller
{
    /**
     * Show the planner settings page with all stages.
     */
    public function index()
    {
        try {
            $stages = Stage::orderBy('order')->get();

            return view('admin.planner-settings', compact('stages'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while loading planner settings. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Bulk-update planner metadata for all stages.
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'stages' => 'required|array',
                'stages.*.id' => 'required|exists:stages,id',
                'stages.*.marks_weight' => 'required|integer|min:0|max:100',
                'stages.*.estimated_study_minutes' => 'required|integer|min:10|max:600',
                'stages.*.importance_score' => 'required|integer|min:1|max:10',
                'stages.*.recommended_week' => 'nullable|integer|min:1|max:52',
            ]);

            $now = now();
            $rows = array_map(function (array $stageData) use ($now) {
                return [
                    'id' => $stageData['id'],
                    'marks_weight' => $stageData['marks_weight'],
                    'estimated_study_minutes' => $stageData['estimated_study_minutes'],
                    'importance_score' => $stageData['importance_score'],
                    'recommended_week' => $stageData['recommended_week'],
                    'updated_at' => $now,
                ];
            }, $validated['stages']);

            Stage::upsert(
                $rows,
                ['id'],
                ['marks_weight', 'estimated_study_minutes', 'importance_score', 'recommended_week', 'updated_at']
            );

            StageSchemaCache::bump();

            return back()->with('success', __('planner.settings_saved'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while updating planner settings. Please try again, or contact support if the problem persists.');
        }
    }
}
