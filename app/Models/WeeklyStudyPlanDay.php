<?php

namespace App\Models;

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
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(WeeklyStudyPlan::class, 'weekly_study_plan_id');
    }
}
