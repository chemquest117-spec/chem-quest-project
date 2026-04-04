<?php

namespace App\Services;

use App\Models\Stage;
use App\Models\StudyPlan;
use App\Models\StudyPlanItem;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PlannerGenerationService
{
    /**
     * Minimum days required between start and exam.
     */
    private const MIN_DAYS_REQUIRED = 3;

    /**
     * Maximum items allowed per single study day.
     */
    private const MAX_ITEMS_PER_DAY = 3;

    /**
     * Generate a study plan for a user based on preferences.
     *
     * @param  array{exam_date: string, start_date: string, preferred_days: array, hours_per_day: float, pace: string}  $preferences
     *
     * @throws \InvalidArgumentException
     */
    public function generate(User $user, array $preferences): StudyPlan
    {
        $startDate = Carbon::parse($preferences['start_date'])->startOfDay();
        $examDate = Carbon::parse($preferences['exam_date'])->startOfDay();
        $preferredDays = $preferences['preferred_days'];
        $hoursPerDay = (float) $preferences['hours_per_day'];
        $pace = $preferences['pace'];

        // ── Validation safeguards ──
        $this->validateSchedule($startDate, $examDate, $preferredDays);

        // Deactivate any existing active plan
        StudyPlan::forUser($user->id)->active()->update([
            'status' => StudyPlan::STATUS_PAUSED,
        ]);

        // Create the plan
        $plan = StudyPlan::create([
            'user_id' => $user->id,
            'exam_date' => $examDate,
            'start_date' => $startDate,
            'preferred_days' => $preferredDays,
            'hours_per_day' => $hoursPerDay,
            'pace' => $pace,
            'total_progress' => 0,
            'status' => StudyPlan::STATUS_ACTIVE,
        ]);

        // Get stages sorted by priority
        $stages = $this->getStagesByPriority();

        if ($stages->isEmpty()) {
            return $plan;
        }

        // Calculate available study slots
        $slots = $this->calculateSlots($startDate, $examDate, $preferredDays);

        if ($slots->isEmpty()) {
            throw new \InvalidArgumentException('No available study days found in the selected period. Try adding more preferred days.');
        }

        // Distribute stages across slots
        $this->distributeStages($plan, $stages, $slots, $hoursPerDay, $pace);

        $plan->calculateProgress();

        return $plan;
    }

    /**
     * Smart reschedule: move missed items to the next available slots.
     */
    public function reschedule(StudyPlan $plan): int
    {
        $missedItems = $plan->missedItems();

        if ($missedItems->isEmpty()) {
            return 0;
        }

        // Get future available slots
        $futureSlots = $this->calculateSlots(
            now()->addDay(),
            $plan->exam_date,
            $plan->preferred_days
        );

        // Remove slots that are already fully booked
        $existingDates = $plan->items()
            ->where('scheduled_date', '>=', now()->toDateString())
            ->where('is_completed', false)
            ->selectRaw('scheduled_date, count(*) as item_count')
            ->groupBy('scheduled_date')
            ->pluck('item_count', 'scheduled_date');

        $availableSlots = $futureSlots->filter(function ($date) use ($existingDates) {
            $dateStr = $date->toDateString();
            $currentCount = $existingDates[$dateStr] ?? 0;

            return $currentCount < self::MAX_ITEMS_PER_DAY;
        })->values();

        $rescheduledCount = 0;
        $slotIndex = 0;

        foreach ($missedItems as $item) {
            if ($slotIndex >= $availableSlots->count()) {
                // No more available slots; try to extend to exam date
                break;
            }

            $item->reschedule($availableSlots[$slotIndex]);
            $rescheduledCount++;
            $slotIndex++;
        }

        return $rescheduledCount;
    }

    /**
     * Calculate all available study date slots between two dates.
     */
    public function calculateSlots(CarbonInterface $start, CarbonInterface $end, array $preferredDays): Collection
    {
        $slots = collect();
        $dayMap = [
            'sun' => Carbon::SUNDAY,
            'mon' => Carbon::MONDAY,
            'tue' => Carbon::TUESDAY,
            'wed' => Carbon::WEDNESDAY,
            'thu' => Carbon::THURSDAY,
            'fri' => Carbon::FRIDAY,
            'sat' => Carbon::SATURDAY,
        ];

        $allowedDays = collect($preferredDays)
            ->map(fn ($d) => $dayMap[strtolower($d)] ?? null)
            ->filter()
            ->toArray();

        $current = $start->copy();
        while ($current->lte($end)) {
            if (in_array($current->dayOfWeek, $allowedDays)) {
                $slots->push($current->copy());
            }
            $current->addDay();
        }

        return $slots;
    }

    /**
     * Validate that the schedule is feasible.
     */
    private function validateSchedule(Carbon $startDate, Carbon $examDate, array $preferredDays): void
    {
        if ($startDate->gte($examDate)) {
            throw new \InvalidArgumentException('Start date must be before exam date.');
        }

        if ($startDate->diffInDays($examDate) < self::MIN_DAYS_REQUIRED) {
            throw new \InvalidArgumentException(
                'At least '.self::MIN_DAYS_REQUIRED.' days are required between start and exam date.'
            );
        }

        if (empty($preferredDays)) {
            throw new \InvalidArgumentException('At least one preferred study day must be selected.');
        }

        $slots = $this->calculateSlots($startDate, $examDate, $preferredDays);
        $stageCount = Stage::count();

        if ($slots->count() < 1 && $stageCount > 0) {
            throw new \InvalidArgumentException(
                'No valid study days found in the selected period. Try adding more preferred days or extending the date range.'
            );
        }
    }

    /**
     * Get all stages sorted by priority (importance & marks weight).
     */
    private function getStagesByPriority(): Collection
    {
        return Stage::orderByDesc('importance_score')
            ->orderByDesc('marks_weight')
            ->orderBy('recommended_week')
            ->orderBy('order')
            ->get();
    }

    /**
     * Distribute stages across available time slots.
     */
    private function distributeStages(
        StudyPlan $plan,
        Collection $stages,
        Collection $slots,
        float $hoursPerDay,
        string $pace
    ): void {
        $minutesPerDay = (int) ($hoursPerDay * 60);
        $paceMultiplier = $plan->getPaceMultiplier();

        $totalSlots = $slots->count();
        $totalStages = $stages->count();

        // Calculate how many slots each stage gets
        $slotsPerStage = max(1, (int) floor($totalSlots / max($totalStages, 1)));

        // Adjust based on pace
        $adjustedSlotsPerStage = max(1, (int) round($slotsPerStage * (1 / $paceMultiplier)));

        $slotIndex = 0;
        $sortOrder = 0;

        foreach ($stages as $stage) {
            // Calculate estimated study time for this stage
            $stageMinutes = $stage->estimated_study_minutes ?: 60;

            // Determine how many days to spread this stage across
            $daysNeeded = max(1, (int) ceil($stageMinutes / $minutesPerDay));

            // Apply pace: intensive = fewer days, light = more days
            $daysNeeded = max(1, (int) round($daysNeeded / $paceMultiplier));

            // Don't exceed remaining available slots
            $daysNeeded = min($daysNeeded, $totalSlots - $slotIndex);

            if ($daysNeeded <= 0 || $slotIndex >= $totalSlots) {
                // Squeeze into the last available slot
                if ($totalSlots > 0) {
                    $lastSlot = $slots[$totalSlots - 1];
                    StudyPlanItem::create([
                        'study_plan_id' => $plan->id,
                        'stage_id' => $stage->id,
                        'scheduled_date' => $lastSlot,
                        'estimated_minutes' => $stageMinutes,
                        'marks_weight' => $stage->marks_weight ?? 0,
                        'sort_order' => $sortOrder++,
                    ]);
                }

                continue;
            }

            // Split study time across allocated days
            $minutesPerSlot = (int) ceil($stageMinutes / $daysNeeded);

            for ($d = 0; $d < $daysNeeded && $slotIndex < $totalSlots; $d++) {
                $remainingMinutes = $stageMinutes - ($d * $minutesPerSlot);
                $allocatedMinutes = min($minutesPerSlot, max($remainingMinutes, 15));

                StudyPlanItem::create([
                    'study_plan_id' => $plan->id,
                    'stage_id' => $stage->id,
                    'scheduled_date' => $slots[$slotIndex],
                    'estimated_minutes' => $allocatedMinutes,
                    'marks_weight' => $stage->marks_weight ?? 0,
                    'sort_order' => $sortOrder++,
                ]);

                $slotIndex++;
            }
        }
    }
}
