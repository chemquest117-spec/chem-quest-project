<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            ->where('passed', true)
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
        if ($totalStages === 0)
            return 0;

        $completed = count($this->completedStageIds());
        return round(($completed / $totalStages) * 100, 1);
    }
}
