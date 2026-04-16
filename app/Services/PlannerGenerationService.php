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
            ->pending()
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

        if ($totalStages === 0 || $totalSlots === 0) {
            return;
        }

        // Reset array keys
        $stagesArray = $stages->values();

        // ── Phase 1: Calculate how many slots each stage gets ──
        // Every stage needs at least 1 study + 1 quiz = 2 slots minimum
        $minSlotsNeeded = $totalStages * 2;

        // Extra slots to distribute as additional study days
        $extraSlots = max(0, $totalSlots - $minSlotsNeeded);

        // Distribute extra slots proportionally by estimated study time
        $totalStudyMinutes = $stagesArray->sum(fn ($s) => $s->estimated_study_minutes ?: 60);

        $stageAllocations = [];
        $allocatedSoFar = 0;

        foreach ($stagesArray as $index => $stage) {
            $stageMinutes = $stage->estimated_study_minutes ?: 60;

            // Base: 1 study slot + 1 quiz slot
            $studySlots = 1;

            // Add extra study slots proportionally
            if ($extraSlots > 0 && $totalStudyMinutes > 0) {
                $proportion = $stageMinutes / $totalStudyMinutes;
                $extraForStage = (int) round($proportion * $extraSlots);

                // For the last stage, give it whatever's left to avoid rounding issues
                if ($index === $totalStages - 1) {
                    $extraForStage = $extraSlots - $allocatedSoFar;
                }

                $studySlots += max(0, $extraForStage);
                $allocatedSoFar += max(0, $extraForStage);
            }

            // Limit study slots based on pace
            // Light pace: allow more days; Intensive: compress
            $maxStudyDays = max(1, (int) round($studySlots / $paceMultiplier));
            $studySlots = max(1, $maxStudyDays);

            $stageAllocations[] = [
                'stage' => $stage,
                'study_slots' => $studySlots,
                'total_slots' => $studySlots + 1, // +1 for quiz
            ];
        }

        // ── Phase 2: Normalize allocations to fit exactly into totalSlots ──
        $totalAllocated = collect($stageAllocations)->sum('total_slots');

        // If we over-allocated, trim study slots from stages with the most
        while ($totalAllocated > $totalSlots && $totalAllocated > $totalStages * 2) {
            // Find the stage with the most study slots
            $maxIdx = 0;
            $maxStudy = 0;
            foreach ($stageAllocations as $i => $alloc) {
                if ($alloc['study_slots'] > $maxStudy) {
                    $maxStudy = $alloc['study_slots'];
                    $maxIdx = $i;
                }
            }
            if ($stageAllocations[$maxIdx]['study_slots'] <= 1) {
                break;
            }
            $stageAllocations[$maxIdx]['study_slots']--;
            $stageAllocations[$maxIdx]['total_slots']--;
            $totalAllocated--;
        }

        // If we under-allocated, add more study slots to stages
        while ($totalAllocated < $totalSlots) {
            // Add to stages round-robin, prioritizing those with higher estimated time
            foreach ($stageAllocations as &$alloc) {
                if ($totalAllocated >= $totalSlots) {
                    break;
                }
                $alloc['study_slots']++;
                $alloc['total_slots']++;
                $totalAllocated++;
            }
            unset($alloc);
        }

        // ── Phase 3: Place items on slots ──
        $slotIndex = 0;
        $sortOrder = 0;

        foreach ($stageAllocations as $alloc) {
            $stage = $alloc['stage'];
            $studySlots = $alloc['study_slots'];
            $stageMinutes = $stage->estimated_study_minutes ?: 60;

            // Split study time across study days
            $minutesPerSlot = max(15, (int) ceil($stageMinutes / max(1, $studySlots)));

            for ($d = 0; $d < $studySlots && $slotIndex < $totalSlots; $d++) {
                $remainingMinutes = $stageMinutes - ($d * $minutesPerSlot);
                $allocatedMinutes = min($minutesPerSlot, max($remainingMinutes, 15));

                StudyPlanItem::create([
                    'study_plan_id' => $plan->id,
                    'stage_id' => $stage->id,
                    'type' => 'study',
                    'scheduled_date' => $slots[$slotIndex],
                    'estimated_minutes' => $allocatedMinutes,
                    'marks_weight' => $d === 0 ? ($stage->marks_weight ?? 0) : 0,
                    'sort_order' => $sortOrder++,
                ]);

                $slotIndex++;
            }

            // Place the quiz on the NEXT available slot (the day after study)
            if ($slotIndex < $totalSlots) {
                StudyPlanItem::create([
                    'study_plan_id' => $plan->id,
                    'stage_id' => $stage->id,
                    'type' => 'quiz',
                    'scheduled_date' => $slots[$slotIndex],
                    'estimated_minutes' => 30,
                    'marks_weight' => 0,
                    'sort_order' => $sortOrder++,
                ]);
                $slotIndex++;
            } else {
                // Squeeze quiz onto the last slot
                StudyPlanItem::create([
                    'study_plan_id' => $plan->id,
                    'stage_id' => $stage->id,
                    'type' => 'quiz',
                    'scheduled_date' => $slots[$totalSlots - 1],
                    'estimated_minutes' => 30,
                    'marks_weight' => 0,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }
}
