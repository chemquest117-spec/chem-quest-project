<?php

namespace App\Models;

use App\Casts\PostgresBoolean;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'total_points',
        'stars',
        'streak',
        'last_activity',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => PostgresBoolean::class,
            'is_banned' => PostgresBoolean::class,
            'last_activity' => 'date',
        ];
    }

    /**
     * Memoization caches for request-lifecycle performance.
     */
    private ?array $cachedCompletedStageIds = null;

    private ?array $cachedFailedStageIds = null;

    private ?array $cachedInProgressStageIds = null;

    public function attempts(): HasMany
    {
        return $this->hasMany(StageAttempt::class);
    }

    public function studyPlans(): HasMany
    {
        return $this->hasMany(StudyPlan::class);
    }

    public function weeklyStudyPlans(): HasMany
    {
        return $this->hasMany(WeeklyStudyPlan::class);
    }

    /**
     * Get the user's currently active study plan.
     */
    public function activeStudyPlan(): ?StudyPlan
    {
        return $this->studyPlans()
            ->where('status', StudyPlan::STATUS_ACTIVE)
            ->latest()
            ->first();
    }

    /**
     * Get stages completed by this user (memoized per request).
     */
    public function completedStageIds(): array
    {
        if ($this->cachedCompletedStageIds === null) {
            $this->cachedCompletedStageIds = $this->attempts()
                ->passed()
                ->pluck('stage_id')
                ->unique()
                ->toArray();
        }

        return $this->cachedCompletedStageIds;
    }

    /**
     * Get stages failed by this user (and not yet passed).
     */
    public function failedStageIds(): array
    {
        if ($this->cachedFailedStageIds === null) {
            $completedIds = $this->completedStageIds();

            $this->cachedFailedStageIds = $this->attempts()
                ->failed()
                ->whereNotNull('completed_at')
                ->whereNotIn('stage_id', $completedIds)
                ->pluck('stage_id')
                ->unique()
                ->toArray();
        }

        return $this->cachedFailedStageIds;
    }

    /**
     * Get stages with currently active attempts.
     */
    public function inProgressStageIds(): array
    {
        if ($this->cachedInProgressStageIds === null) {
            $this->cachedInProgressStageIds = $this->attempts()
                ->whereNull('completed_at')
                ->pluck('stage_id')
                ->unique()
                ->toArray();
        }

        return $this->cachedInProgressStageIds;
    }

    /**
     * Get the current stage (first uncompleted stage).
     */
    public function currentStage()
    {
        $completedIds = $this->completedStageIds();

        return Stage::orderBy('order')
            ->whereNotIn('id', $completedIds)
            ->first();
    }

    /**
     * Get overall progress percentage.
     */
    public function progressPercentage(): float
    {
        $totalStages = Stage::count();
        if ($totalStages === 0) {
            return 0;
        }

        $completed = count($this->completedStageIds());

        return round(($completed / $totalStages) * 100, 1);
    }
}
