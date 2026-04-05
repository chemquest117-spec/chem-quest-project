<?php

namespace App\Models;

use App\Casts\PostgresBoolean;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $study_plan_id
 * @property int $stage_id
 * @property Carbon $scheduled_date
 * @property int $estimated_minutes
 * @property int $marks_weight
 * @property int $sort_order
 * @property bool $is_completed
 * @property Carbon|null $completed_at
 * @property bool $auto_rescheduled
 * @property int $reschedule_count
 * @property string|null $notes
 * @property-read StudyPlan $studyPlan
 * @property-read Stage $stage
 */
class StudyPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_plan_id',
        'stage_id',
        'scheduled_date',
        'estimated_minutes',
        'marks_weight',
        'sort_order',
        'is_completed',
        'completed_at',
        'auto_rescheduled',
        'reschedule_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_at' => 'datetime',
            'is_completed' => PostgresBoolean::class,
            'auto_rescheduled' => PostgresBoolean::class,
            'estimated_minutes' => 'integer',
            'marks_weight' => 'integer',
            'sort_order' => 'integer',
            'reschedule_count' => 'integer',
        ];
    }

    // ── Relationships ──

    public function studyPlan(): BelongsTo
    {
        return $this->belongsTo(StudyPlan::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    // ── Scopes ──

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', PostgresBoolean::asQueryValue(true));
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', PostgresBoolean::asQueryValue(false));
    }

    public function scopeMissed($query)
    {
        return $query->where('is_completed', PostgresBoolean::asQueryValue(false))
            ->where('scheduled_date', '<', now()->toDateString());
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('scheduled_date', $date);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>=', now()->toDateString())
            ->where('is_completed', PostgresBoolean::asQueryValue(false));
    }

    // ── Actions ──

    /**
     * Mark this item as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $this->studyPlan->calculateProgress();
    }

    /**
     * Mark this item as not completed (undo).
     */
    public function markIncomplete(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);

        $this->studyPlan->calculateProgress();
    }

    /**
     * Reschedule this item to a new date.
     */
    public function reschedule(Carbon $newDate): void
    {
        $this->update([
            'scheduled_date' => $newDate,
            'auto_rescheduled' => true,
            'reschedule_count' => $this->reschedule_count + 1,
        ]);
    }

    /**
     * Check if this item is overdue (past scheduled date and not completed).
     */
    public function isOverdue(): bool
    {
        return ! $this->is_completed && $this->scheduled_date->isPast();
    }
}
