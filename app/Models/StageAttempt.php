<?php

namespace App\Models;

use App\Casts\PostgresBoolean;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $stage_id
 * @property int $score
 * @property int $total_questions
 * @property bool $passed
 * @property int $time_spent_seconds
 * @property Carbon $started_at
 * @property Carbon $completed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Stage $stage
 * @property-read Collection|AttemptAnswer[] $answers
 * @property int|null $total_attempts
 * @property int|null $passed_attempts
 * @property float|null $sum_score
 * @property float|null $avg_questions
 * @property int|null $passed_count
 * @property float|null $avg_time
 */
class StageAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stage_id',
        'score',
        'total_questions',
        'passed',
        'time_spent_seconds',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'passed' => PostgresBoolean::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    /**
     * Get score as percentage.
     */
    public function getScorePercentageAttribute(): float
    {
        if ($this->total_questions === 0) {
            return 0;
        }

        return round(($this->score / $this->total_questions) * 100, 1);
    }

    /**
     * Check if this is a first-time pass for this stage.
     */
    public function isFirstPass(): bool
    {
        if (! $this->passed) {
            return false;
        }

        return ! StageAttempt::where('user_id', $this->user_id)
            ->where('stage_id', $this->stage_id)
            ->passed()
            ->where('id', '!=', $this->id)
            ->exists();
    }

    /**
     * Scope: only passed attempts.
     */
    public function scopePassed($query)
    {
        return $query->where('passed', PostgresBoolean::asQueryValue(true));
    }

    /**
     * Scope: only failed attempts.
     */
    public function scopeFailed($query)
    {
        return $query->where('passed', PostgresBoolean::asQueryValue(false));
    }
}
