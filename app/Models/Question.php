<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
     protected $fillable = [
          'stage_id',
          'question_text',
          'question_text_ar',
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
          $field = 'option_' . strtolower($option);
          $fieldAr = $field . '_ar';
          return app()->getLocale() === 'ar' && $this->{$fieldAr} ? $this->{$fieldAr} : $this->{$field};
     }

     public function getTranslatedDifficulty(): string
     {
          return app()->getLocale() === 'ar' && $this->difficulty_ar ? $this->difficulty_ar : $this->difficulty;
     }

     /**
      * Scope to randomize question order.
      */
     public function scopeRandomized($query)
     {
          return $query->inRandomOrder();
     }
}
