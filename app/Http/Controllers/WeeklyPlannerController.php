<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\WeeklyStudyPlan;
use App\Models\WeeklyStudyPlanDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeeklyPlannerController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();

            // Ensure user has plans for the first 5 stages
            $stages = Stage::orderBy('order')->take(5)->get();
            foreach ($stages as $index => $stage) {
                $weekNumber = $index + 1;
                WeeklyStudyPlan::firstOrCreate(
                    ['user_id' => $user->id, 'week_number' => $weekNumber],
                    ['stage_id' => $stage->id, 'status' => 'active']
                );
            }

            $plans = WeeklyStudyPlan::with('days.plan', 'stage')->where('user_id', $user->id)->orderBy('week_number')->get();

            return view('weekly-planner.index', compact('plans'));
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while showing the weekly planner. Please try again, or contact support if the problem persists.');
        }
    }

    public function assignDay(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:weekly_study_plans,id',
                'day_name' => 'required|in:sat,sun,mon,tue,wed,thu,fri',
                'action_type' => 'required|in:study,test',
            ]);

            $plan = WeeklyStudyPlan::where('user_id', Auth::id())->findOrFail($request->plan_id);

            WeeklyStudyPlanDay::updateOrCreate(
                ['weekly_study_plan_id' => $plan->id, 'action_type' => $request->action_type],
                ['day_name' => $request->day_name, 'is_completed' => false, 'completed_at' => null]
            );

            return back()->with('status', 'planner-updated');
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while assigning a day to the study plan. Please try again, or contact support if the problem persists.');
        }
    }

    public function clearDay(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:weekly_study_plans,id',
                'action_type' => 'required|in:study,test',
            ]);

            $plan = WeeklyStudyPlan::where('user_id', Auth::id())->findOrFail($request->plan_id);

            WeeklyStudyPlanDay::where('weekly_study_plan_id', $plan->id)
                ->where('action_type', $request->action_type)
                ->delete();

            return back()->with('status', 'planner-updated');
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while clearing the day from planner. Please try again, or contact support if the problem persists.');
        }
    }

    public function toggleComplete(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:weekly_study_plans,id',
                'action_type' => 'required|in:study,test', // usually just study is manually togglable, test is auto
            ]);

            $plan = WeeklyStudyPlan::where('user_id', Auth::id())->findOrFail($request->plan_id);
            $day = $plan->days()->where('action_type', $request->action_type)->first();

            if ($day) {
                $day->update([
                    'is_completed' => ! $day->is_completed,
                    'completed_at' => ! $day->is_completed ? now() : null,
                ]);

                // Re-eval plan status
                $this->updatePlanStatus($plan);
            }

            return back()->with('status', 'planner-updated');
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while toggling the completion of a day in the study plan. Please try again, or contact support if the problem persists.');
        }
    }

    public function resetWeek(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:weekly_study_plans,id',
            ]);

            $plan = WeeklyStudyPlan::where('user_id', Auth::id())->findOrFail($request->plan_id);
            $plan->days()->delete();
            $plan->update(['status' => 'active']);

            return back()->with('status', 'planner-updated');
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while resetting the study plan. Please try again, or contact support if the problem persists.');
        }
    }

    protected function updatePlanStatus(WeeklyStudyPlan $plan)
    {
        try {
            $studyDay = $plan->days()->where('action_type', 'study')->first();
            $testDay = $plan->days()->where('action_type', 'test')->first();

            if ($studyDay?->is_completed && $testDay?->is_completed) {
                $plan->update(['status' => 'completed']);
            } else {
                $plan->update(['status' => 'active']);
            }
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while updating the study plan status. Please try again, or contact support if the problem persists.');
        }
    }
}
