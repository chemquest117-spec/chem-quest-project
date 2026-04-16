<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $stage_id
 * @property string $question_text
 * @property string|null $question_text_ar
 * @property string|null $type
 * @property string|null $option_a
 * @property string|null $option_a_ar
 * @property string|null $option_b
 * @property string|null $option_b_ar
 * @property string|null $option_c
 * @property string|null $option_c_ar
 * @property string|null $option_d
 * @property string|null $option_d_ar
 * @property string|null $correct_answer
 * @property string|null $correct_answer_ar
 * @property string|null $difficulty
 * @property string|null $difficulty_ar
 * @property string|null $topic
 * @property string|null $topic_ar
 * @property string|null $explanation
 * @property string|null $explanation_ar
 * @property string|null $expected_answer
 * @property string|null $expected_answer_ar
 * @property array|null $expected_answers
 * @property string|null $image
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Stage $stage
 * @property-read Collection|AttemptAnswer[] $attemptAnswers
 */
class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'stage_id',
        'question_text',
        'question_text_ar',
        'type',
        'option_a',
        'option_a_ar',
        'option_b',
        'option_b_ar',
        'option_c',
        'option_c_ar',
        'option_d',
        'option_d_ar',
        'correct_answer',
        'correct_answer_ar',
        'difficulty',
        'difficulty_ar',
        'topic',
        'topic_ar',
        'explanation',
        'explanation_ar',
        'expected_answer',
        'expected_answer_ar',
        'expected_answers',
        'image',
    ];

    protected $casts = [
        'expected_answers' => 'array',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function getTranslatedQuestionText(): string
    {
        return app()->getLocale() === 'ar' && $this->question_text_ar ? $this->question_text_ar : $this->question_text;
    }

    public function getTranslatedOption(string $option): string
    {
        $field = 'option_'.strtolower($option);
        $fieldAr = $field.'_ar';

        return (app()->getLocale() === 'ar' && $this->{$fieldAr} ? $this->{$fieldAr} : $this->{$field}) ?? '';
    }

    public function getTranslatedDifficulty(): string
    {
        return (app()->getLocale() === 'ar' && $this->difficulty_ar ? $this->difficulty_ar : $this->difficulty) ?? '';
    }

    public function getTranslatedTopic(): ?string
    {
        if (! $this->topic) {
            return null;
        }

        return app()->getLocale() === 'ar' && $this->topic_ar ? $this->topic_ar : $this->topic;
    }

    public function getTranslatedExplanation(): ?string
    {
        if (! $this->explanation) {
            return null;
        }

        return app()->getLocale() === 'ar' && $this->explanation_ar ? $this->explanation_ar : $this->explanation;
    }

    public function getTranslatedExpectedAnswer(): ?string
    {
        if (! $this->expected_answer) {
            return null;
        }

        return app()->getLocale() === 'ar' && $this->expected_answer_ar ? $this->expected_answer_ar : $this->expected_answer;
    }

    /**
     * Check if this is a multiple choice question.
     */
    public function isMcq(): bool
    {
        return $this->type === 'mcq' || $this->type === null;
    }

    /**
     * Check if this is a complete (fill-in-the-blank numeric) question.
     */
    public function isComplete(): bool
    {
        return $this->type === 'complete';
    }

    /**
     * @deprecated Use isComplete() instead. Kept for backward compatibility.
     */
    public function isEssay(): bool
    {
        return $this->isComplete();
    }

    /**
     * Get the number of blanks (____) in the question text.
     */
    public function getBlankCount(): int
    {
        return preg_match_all('/_{3,}/', $this->getTranslatedQuestionText());
    }

    /**
     * Scope to randomize question order.
     */
    public function scopeRandomized($query)
    {
        return $query->inRandomOrder();
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by topic.
     */
    public function scopeOfTopic($query, string $topic)
    {
        return $query->where('topic', $topic);
    }

    /**
     * Scope to filter by difficulty.
     */
    public function scopeOfDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }
}
