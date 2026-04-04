<?php

namespace App\Models;

use App\Casts\PostgresBoolean;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttemptAnswer extends Model
{
    protected $fillable = [
        'stage_attempt_id',
        'question_id',
        'selected_answer',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => PostgresBoolean::class,
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(StageAttempt::class, 'stage_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
