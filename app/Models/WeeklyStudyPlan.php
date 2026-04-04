<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyStudyPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stage_id',
        'week_number',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(WeeklyStudyPlanDay::class);
    }

    public function getStudyDayAttribute()
    {
        return $this->days->where('action_type', 'study')->first();
    }

    public function getTestDayAttribute()
    {
        return $this->days->where('action_type', 'test')->first();
    }
}
