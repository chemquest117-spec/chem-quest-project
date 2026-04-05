<?php

namespace App\Models;

use App\Casts\PostgresBoolean;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyStudyPlanDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_study_plan_id',
        'day_name',
        'action_type',
        'title',
        'start_time',
        'end_time',
        'notes',
        'color',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => PostgresBoolean::class,
        'completed_at' => 'datetime',
    ];

    /** Available event colors */
    public const COLORS = [
        'indigo' => ['bg' => 'bg-indigo-500/15', 'border' => 'border-l-indigo-500', 'text' => 'text-indigo-300', 'dot' => 'bg-indigo-400'],
        'emerald' => ['bg' => 'bg-emerald-500/15', 'border' => 'border-l-emerald-500', 'text' => 'text-emerald-300', 'dot' => 'bg-emerald-400'],
        'blue' => ['bg' => 'bg-blue-500/15', 'border' => 'border-l-blue-500', 'text' => 'text-blue-300', 'dot' => 'bg-blue-400'],
        'purple' => ['bg' => 'bg-purple-500/15', 'border' => 'border-l-purple-500', 'text' => 'text-purple-300', 'dot' => 'bg-purple-400'],
        'amber' => ['bg' => 'bg-amber-500/15', 'border' => 'border-l-amber-500', 'text' => 'text-amber-300', 'dot' => 'bg-amber-400'],
        'rose' => ['bg' => 'bg-rose-500/15', 'border' => 'border-l-rose-500', 'text' => 'text-rose-300', 'dot' => 'bg-rose-400'],
        'cyan' => ['bg' => 'bg-cyan-500/15', 'border' => 'border-l-cyan-500', 'text' => 'text-cyan-300', 'dot' => 'bg-cyan-400'],
        'orange' => ['bg' => 'bg-orange-500/15', 'border' => 'border-l-orange-500', 'text' => 'text-orange-300', 'dot' => 'bg-orange-400'],
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(WeeklyStudyPlan::class, 'weekly_study_plan_id');
    }

    /**
     * Get the formatted time range (e.g. "8:00 AM - 9:30 AM").
     */
    public function getTimeRangeAttribute(): string
    {
        if (! $this->start_time || ! $this->end_time) {
            return '';
        }

        $start = Carbon::parse($this->start_time)->format('g:i A');
        $end = Carbon::parse($this->end_time)->format('g:i A');

        return "{$start} - {$end}";
    }

    /**
     * Get the duration in minutes.
     */
    public function getDurationMinutesAttribute(): int
    {
        if (! $this->start_time || ! $this->end_time) {
            return 60;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return max(15, (int) $start->diffInMinutes($end));
    }

    /**
     * Get the display title (falls back to stage name from the plan).
     */
    public function getDisplayTitleAttribute(): string
    {
        if ($this->title) {
            return $this->title;
        }

        $type = $this->action_type === 'study' ? '📘 Study' : '📝 Quiz';
        $stage = $this->plan?->stage?->getTranslatedTitle() ?? '';

        return "{$type}: {$stage}";
    }

    /**
     * Get color CSS classes.
     */
    public function getColorClassesAttribute(): array
    {
        return self::COLORS[$this->color] ?? self::COLORS['indigo'];
    }
}
