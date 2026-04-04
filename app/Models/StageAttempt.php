<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
          'passed' => 'boolean',
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
          if ($this->total_questions === 0)
               return 0;
          return round(($this->score / $this->total_questions) * 100, 1);
     }

     /**
      * Check if this is a first-time pass for this stage.
      */
     public function isFirstPass(): bool
     {
          if (!$this->passed)
               return false;

          return !StageAttempt::where('user_id', $this->user_id)
               ->where('stage_id', $this->stage_id)
               ->where('passed', true)
               ->where('id', '!=', $this->id)
               ->exists();
     }
}
