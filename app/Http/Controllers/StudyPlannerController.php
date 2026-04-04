<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\StudyPlan;
use App\Models\StudyPlanItem;
use App\Services\PlannerGenerationService;
use App\Services\ProgressSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StudyPlannerController extends Controller
{
    public function __construct(
        private PlannerGenerationService $generationService,
        private ProgressSyncService $progressService,
    ) {}

    /**
     * Show the planner dashboard or setup wizard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $activePlan = $user->activeStudyPlan();

        if ($activePlan) {
            $activePlan->load(['items.stage']);

            // Sync any completed stages
            $this->progressService->syncAllForPlan($activePlan);
            $activePlan->refresh();

            // Group items by week
            $itemsByWeek = $activePlan->items
                ->sortBy('scheduled_date')
                ->groupBy(function ($item) {
                    return $item->scheduled_date->startOfWeek()->format('Y-m-d');
                });

            $todayItems = $activePlan->todayItems();
            $todayItems->load('stage');
            $missedItems = $activePlan->missedItems();
            $missedItems->load('stage');

            return view('planner.index', compact(
                'activePlan',
                'itemsByWeek',
                'todayItems',
                'missedItems',
            ));
        }

        // Show history of completed plans
        $pastPlans = $user->studyPlans()
            ->whereIn('status', [StudyPlan::STATUS_COMPLETED, StudyPlan::STATUS_EXPIRED, StudyPlan::STATUS_PAUSED])
            ->latest()
            ->take(5)
            ->get();

        return view('planner.index', [
            'activePlan' => null,
            'pastPlans' => $pastPlans,
        ]);
    }

    /**
     * Show the plan creation wizard.
     */
    public function create()
    {
        $stages = Cache::remember('all_stages', 86400, function () {
            return Stage::orderBy('order')->get();
        });

        return view('planner.create', compact('stages'));
    }

    /**
     * Generate a new study plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'exam_date' => 'required|date|after:start_date',
            'preferred_days' => 'required|array|min:1',
            'preferred_days.*' => 'string|in:sun,mon,tue,wed,thu,fri,sat',
            'hours_per_day' => 'required|numeric|min:0.5|max:12',
            'pace' => 'required|in:light,medium,intensive',
        ]);

        try {
            $plan = $this->generationService->generate($request->user(), $validated);

            return redirect()
                ->route('planner.show', $plan)
                ->with('success', __('planner.plan_created'));
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show a specific study plan's calendar view.
     */
    public function show(Request $request, StudyPlan $studyPlan)
    {
        // Authorization: plan belongs to user
        if ($studyPlan->user_id !== $request->user()->id) {
            abort(403);
        }

        $studyPlan->load(['items.stage']);

        $itemsByWeek = $studyPlan->items
            ->sortBy('scheduled_date')
            ->groupBy(function ($item) {
                return $item->scheduled_date->startOfWeek()->format('Y-m-d');
            });

        $itemsByDate = $studyPlan->items
            ->sortBy(['scheduled_date', 'sort_order'])
            ->groupBy(fn ($item) => $item->scheduled_date->toDateString());

        return view('planner.show', compact('studyPlan', 'itemsByWeek', 'itemsByDate'));
    }

    /**
     * Toggle a study plan item completion.
     */
    public function toggleItem(Request $request, StudyPlanItem $studyPlanItem)
    {
        // Authorization
        if ($studyPlanItem->studyPlan->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($studyPlanItem->is_completed) {
            $studyPlanItem->markIncomplete();
            $message = __('planner.item_unmarked');
        } else {
            $studyPlanItem->markCompleted();
            $message = __('planner.item_completed');
        }

        return back()->with('success', $message);
    }

    /**
     * Reschedule missed items.
     */
    public function reschedule(Request $request, StudyPlan $studyPlan)
    {
        if ($studyPlan->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($studyPlan->status !== StudyPlan::STATUS_ACTIVE) {
            return back()->with('error', __('planner.plan_not_active'));
        }

        $count = $this->generationService->reschedule($studyPlan);

        if ($count === 0) {
            return back()->with('info', __('planner.nothing_to_reschedule'));
        }

        return back()->with('success', __('planner.rescheduled', ['count' => $count]));
    }

    /**
     * Delete a study plan.
     */
    public function destroy(Request $request, StudyPlan $studyPlan)
    {
        if ($studyPlan->user_id !== $request->user()->id) {
            abort(403);
        }

        $studyPlan->delete();

        return redirect()->route('planner.index')
            ->with('success', __('planner.plan_deleted'));
    }

    /**
     * Update notes on a study plan item.
     */
    public function updateNotes(Request $request, StudyPlanItem $studyPlanItem)
    {
        if ($studyPlanItem->studyPlan->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $studyPlanItem->update($validated);

        return back()->with('success', __('planner.notes_saved'));
    }
}
