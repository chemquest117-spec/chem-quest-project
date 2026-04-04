<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(StageAttempt::class);
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
     */
    public function isUnlockedFor($user): bool
    {
        if ($this->order === 1) {
            return true;
        }

        $previousStage = Stage::where('order', $this->order - 1)->first();

        if (! $previousStage) {
            return true;
        }

        return $previousStage->attempts()
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->exists();
    }

    /**
     * Check if stage is completed by user.
     */
    public function isCompletedBy($user): bool
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->exists();
    }

    /**
     * Get best attempt for a user.
     */
    public function bestAttemptFor($user)
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->orderByDesc('score')
            ->first();
    }
}
