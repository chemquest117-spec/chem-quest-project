<?php

namespace App\Models;

use App\Casts\PostgresBoolean;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $exam_date
 * @property Carbon $start_date
 * @property array $preferred_days
 * @property float $hours_per_day
 * @property int $total_progress
 * @property string $status
 * @property string $pace
 * @property-read User $user
 * @property-read Collection|StudyPlanItem[] $items
 */
class StudyPlan extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_EXPIRED = 'expired';

    public const PACE_LIGHT = 'light';

    public const PACE_MEDIUM = 'medium';

    public const PACE_INTENSIVE = 'intensive';

    protected $fillable = [
        'user_id',
        'exam_date',
        'start_date',
        'preferred_days',
        'hours_per_day',
        'pace',
        'total_progress',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'start_date' => 'date',
            'preferred_days' => 'array',
            'hours_per_day' => 'decimal:1',
            'total_progress' => 'integer',
        ];
    }

    // ── Relationships ──

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StudyPlanItem::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ── Computed Attributes ──

    /**
     * Recalculate and persist total_progress from items.
     */
    public function calculateProgress(): int
    {
        $total = $this->items()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->items()->completed()->count();
        $progress = (int) round(($completed / $total) * 100);

        $this->update(['total_progress' => $progress]);

        // Auto-complete plan if 100%
        if ($progress >= 100 && $this->status === self::STATUS_ACTIVE) {
            $this->update(['status' => self::STATUS_COMPLETED]);
        }

        return $progress;
    }

    /**
     * Check if the exam date has passed.
     */
    public function isOverdue(): bool
    {
        return $this->exam_date->isPast();
    }

    /**
     * Days remaining until exam.
     */
    public function daysRemaining(): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($this->exam_date, false));
    }

    /**
     * Get items that were scheduled in the past but not completed.
     */
    public function missedItems()
    {
        return $this->items()
            ->pending()
            ->where('scheduled_date', '<', now()->toDateString())
            ->orderBy('scheduled_date')
            ->get();
    }

    /**
     * Get today's scheduled items.
     */
    public function todayItems()
    {
        return $this->items()
            ->where('scheduled_date', now()->toDateString())
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get upcoming items (today + future).
     */
    public function upcomingItems()
    {
        return $this->items()
            ->where('scheduled_date', '>=', now()->toDateString())
            ->pending()
            ->orderBy('scheduled_date')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get pace multiplier for generation algorithm.
     */
    public function getPaceMultiplier(): float
    {
        return match ($this->pace) {
            self::PACE_LIGHT => 0.7,
            self::PACE_INTENSIVE => 1.5,
            default => 1.0,
        };
    }
}
