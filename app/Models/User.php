<?php

namespace App\Models;

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
            'is_admin' => 'boolean',
            'is_banned' => 'boolean',
            'last_activity' => 'date',
        ];
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(StageAttempt::class);
    }

    /**
     * Get stages completed by this user.
     */
    public function completedStageIds(): array
    {
        return $this->attempts()
            ->passed()
            ->pluck('stage_id')
            ->unique()
            ->toArray();
    }

    /**
     * Get stages failed by this user (and not yet passed).
     */
    public function failedStageIds(): array
    {
        $completedIds = $this->completedStageIds();

        return $this->attempts()
            ->failed()
            ->whereNotNull('completed_at')
            ->whereNotIn('stage_id', $completedIds)
            ->pluck('stage_id')
            ->unique()
            ->toArray();
    }

    /**
     * Get stages with currently active attempts.
     */
    public function inProgressStageIds(): array
    {
        return $this->attempts()
            ->whereNull('completed_at')
            ->pluck('stage_id')
            ->unique()
            ->toArray();
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
