<?php

namespace App\Models;

use App\Casts\PostgresBoolean;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $stage_attempt_id
 * @property int $question_id
 * @property string|null $selected_answer
 * @property bool $is_correct
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read StageAttempt $attempt
 * @property-read Question $question
 * @property int|null $total_attempts
 * @property int|null $wrong_count
 */
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
