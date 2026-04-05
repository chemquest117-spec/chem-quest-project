<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\WeeklyStudyPlan;
use App\Models\WeeklyStudyPlanDay;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WeeklyPlannerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // Ensure user has plans for all stages
            $stages = Stage::orderBy('order')->get();
            foreach ($stages as $index => $stage) {
                $weekNumber = $index + 1;
                WeeklyStudyPlan::firstOrCreate(
                    ['user_id' => $user->id, 'week_number' => $weekNumber],
                    ['stage_id' => $stage->id, 'status' => 'active']
                );
            }

            // Determine which week to show
            $currentWeek = (int) $request->get('week', 1);
            $maxWeek = max(1, $stages->count());
            $currentWeek = max(1, min($currentWeek, $maxWeek));

            $plans = WeeklyStudyPlan::with(['days.plan.stage', 'stage'])
                ->where('user_id', $user->id)
                ->orderBy('week_number')
                ->get();

            $activePlan = $plans->firstWhere('week_number', $currentWeek);

            // Calendar days (Sat-Fri)
            $calendarDays = ['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'];

            // Calculate dates for this week view
            $today = Carbon::today();
            $weekOffset = $currentWeek - 1;
            $currentSaturday = $today->copy()->startOfWeek(Carbon::SATURDAY);
            $weekStart = $currentSaturday->copy()->addWeeks($weekOffset);
            $weekEnd = $weekStart->copy()->addDays(6);

            $dayMap = ['sat' => 0, 'sun' => 1, 'mon' => 2, 'tue' => 3, 'wed' => 4, 'thu' => 5, 'fri' => 6];
            $dayDates = [];
            foreach ($calendarDays as $day) {
                $dayDates[$day] = $weekStart->copy()->addDays($dayMap[$day]);
            }

            // Build events grouped by day
            $eventsByDay = [];
            foreach ($calendarDays as $day) {
                $eventsByDay[$day] = [];
            }

            if ($activePlan) {
                foreach ($activePlan->days as $dayEvent) {
                    $dayName = $dayEvent->day_name;
                    if (isset($eventsByDay[$dayName])) {
                        $eventsByDay[$dayName][] = $dayEvent;
                    }
                }
            }

            // Sort events by start_time
            foreach ($eventsByDay as $day => &$events) {
                usort($events, function ($a, $b) {
                    if (! $a->start_time) {
                        return 1;
                    }
                    if (! $b->start_time) {
                        return -1;
                    }

                    return strcmp($a->start_time, $b->start_time);
                });
            }
            unset($events);

            // Time slots for the grid (7AM - 10PM)
            $timeSlots = [];
            for ($h = 7; $h <= 22; $h++) {
                $timeSlots[] = sprintf('%02d:00', $h);
            }

            // Progress stats for active plan
            $totalEvents = 0;
            $completedEvents = 0;
            if ($activePlan) {
                $totalEvents = $activePlan->days->count();
                $completedEvents = $activePlan->days->where('is_completed', true)->count();
            }

            $colorOptions = WeeklyStudyPlanDay::COLORS;

            return view('weekly-planner.index', compact(
                'plans',
                'activePlan',
                'calendarDays',
                'currentWeek',
                'maxWeek',
                'weekStart',
                'weekEnd',
                'dayDates',
                'eventsByDay',
                'timeSlots',
                'totalEvents',
                'completedEvents',
                'stages',
                'colorOptions',
            ));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while showing the weekly planner. Please try again.');
        }
    }

    /**
     * Store a new event.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'plan_id' => 'required|exists:weekly_study_plans,id',
                'day_name' => 'required|in:sat,sun,mon,tue,wed,thu,fri',
                'action_type' => 'required|in:study,test',
                'title' => 'nullable|string|max:255',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'notes' => 'nullable|string|max:1000',
                'color' => 'nullable|string|in:' . implode(',', array_keys(WeeklyStudyPlanDay::COLORS)),
            ]);

            $plan = WeeklyStudyPlan::where('user_id', Auth::id())->findOrFail($validated['plan_id']);

            WeeklyStudyPlanDay::create([
                'weekly_study_plan_id' => $plan->id,
                'day_name' => $validated['day_name'],
                'action_type' => $validated['action_type'],
                'title' => $validated['title'] ?? null,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'notes' => $validated['notes'] ?? null,
                'color' => $validated['color'] ?? ($validated['action_type'] === 'study' ? 'indigo' : 'purple'),
                'is_completed' => false,
            ]);

            return back()->with('success', 'Task added successfully!');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to add task. Please try again.');
        }
    }

    /**
     * Update an existing event.
     */
    public function update(Request $request, WeeklyStudyPlanDay $event)
    {
        try {
            // Verify ownership
            $plan = $event->plan;
            if ($plan->user_id !== Auth::id()) {
                abort(403);
            }

            $validated = $request->validate([
                'day_name' => 'sometimes|in:sat,sun,mon,tue,wed,thu,fri',
                'action_type' => 'sometimes|in:study,test',
                'title' => 'nullable|string|max:255',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'sometimes|date_format:H:i',
                'notes' => 'nullable|string|max:1000',
                'color' => 'nullable|string|in:' . implode(',', array_keys(WeeklyStudyPlanDay::COLORS)),
            ]);

            $event->update($validated);

            return back()->with('success', 'Task updated!');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update task.');
        }
    }

    /**
     * Delete an event.
     */
    public function destroy(WeeklyStudyPlanDay $event)
    {
        try {
            if ($event->plan->user_id !== Auth::id()) {
                abort(403);
            }

            $event->delete();

            $this->updatePlanStatus($event->plan);

            return back()->with('success', 'Task removed.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Failed to remove task.');
        }
    }

    /**
     * Toggle an event's completion.
     */
    public function toggleComplete(Request $request)
    {
        try {
            $request->validate([
                'event_id' => 'required|exists:weekly_study_plan_days,id',
            ]);

            $event = WeeklyStudyPlanDay::findOrFail($request->event_id);

            if ($event->plan->user_id !== Auth::id()) {
                abort(403);
            }

            $event->update([
                'is_completed' => ! $event->is_completed,
                'completed_at' => ! $event->is_completed ? now() : null,
            ]);

            $this->updatePlanStatus($event->plan);

            return back()->with('success', $event->is_completed ? 'Task completed!' : 'Task unmarked.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Failed to toggle task.');
        }
    }

    /**
     * Reset a week's plan (remove all events).
     */
    public function resetWeek(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:weekly_study_plans,id',
            ]);

            $plan = WeeklyStudyPlan::where('user_id', Auth::id())->findOrFail($request->plan_id);
            $plan->days()->delete();
            $plan->update(['status' => 'active']);

            return back()->with('success', 'Week plan reset.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Failed to reset week.');
        }
    }

    // Legacy compatibility routes
    public function assignDay(Request $request)
    {
        // Redirect to the new store method with defaults
        $request->merge([
            'start_time' => $request->start_time ?? '09:00',
            'end_time' => $request->end_time ?? '10:00',
        ]);

        return $this->store($request);
    }

    public function clearDay(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:weekly_study_plans,id',
                'action_type' => 'required|in:study,test',
            ]);

            $plan = WeeklyStudyPlan::where('user_id', Auth::id())->findOrFail($request->plan_id);

            $plan->days()->where('action_type', $request->action_type)->delete();

            return back()->with('success', 'Tasks cleared.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Failed to clear tasks.');
        }
    }

    protected function updatePlanStatus(WeeklyStudyPlan $plan): void
    {
        $plan->refresh();
        $totalDays = $plan->days()->count();
        $completedDays = $plan->days()->where('is_completed', true)->count();

        if ($totalDays > 0 && $totalDays === $completedDays) {
            $plan->update(['status' => 'completed']);
        } else {
            $plan->update(['status' => 'active']);
        }
    }
}
