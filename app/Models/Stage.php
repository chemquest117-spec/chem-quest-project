<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Stage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'title_ar',
        'description',
        'description_ar',
        'order',
        'time_limit_minutes',
        'passing_percentage',
        'points_reward',
        'marks_weight',
        'estimated_study_minutes',
        'importance_score',
        'recommended_week',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(StageAttempt::class);
    }

    public function studyPlanItems(): HasMany
    {
        return $this->hasMany(StudyPlanItem::class);
    }

    public function weeklyStudyPlans(): HasMany
    {
        return $this->hasMany(WeeklyStudyPlan::class);
    }

    public function getTranslatedTitle(): string
    {
        return app()->getLocale() === 'ar' && $this->title_ar ? $this->title_ar : $this->title;
    }

    public function getTranslatedDescription()
    {
        return app()->getLocale() === 'ar' && $this->description_ar ? $this->description_ar : $this->description;
    }

    /**
     * Check if stage is unlocked for a given user.
     * Stage 1 (order=1) is always unlocked.
     * Other stages require the previous stage to be passed.
     *
     * @param  Collection|null  $allStages  Preloaded stages collection to avoid N+1 queries.
     * @param  array|null  $completedIds  Precomputed completed stage IDs.
     */
    public function isUnlockedFor($user, $allStages = null, ?array $completedIds = null): bool
    {
        if ($this->order === 1) {
            return true;
        }

        // Use preloaded collection if available, otherwise query
        if ($allStages) {
            $previousStage = $allStages->firstWhere('order', $this->order - 1);
        } else {
            $previousStage = Stage::where('order', $this->order - 1)->first();
        }

        if (! $previousStage) {
            return true;
        }

        // Use precomputed completed IDs if available
        if ($completedIds !== null) {
            return in_array($previousStage->id, $completedIds);
        }

        return $previousStage->attempts()
            ->where('user_id', $user->id)
            ->passed()
            ->exists();
    }

    /**
     * Check if stage is completed by user.
     */
    public function isCompletedBy($user): bool
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->passed()
            ->exists();
    }

    /**
     * Get best attempt for a user.
     */
    public function bestAttemptFor($user)
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->passed()
            ->orderByDesc('score')
            ->first();
    }
}
