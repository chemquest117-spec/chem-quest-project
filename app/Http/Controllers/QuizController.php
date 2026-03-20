<?php

namespace App\Http\Controllers;

use App\Models\AttemptAnswer;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Notifications\StageCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class QuizController extends Controller
{
     /**
      * Start a new quiz attempt for a stage.
      */
     public function start(Request $request, Stage $stage)
     {
          $user = $request->user();

          // Validate stage is unlocked
          if (!$stage->isUnlockedFor($user)) {
               return redirect()->route('stages.index')
                    ->with('error', __('messages.stage_locked'));
          }

          // Check minimum questions
          if ($stage->questions()->count() === 0) {
               return redirect()->route('stages.show', $stage)
                    ->with('error', __('messages.error'));
          }

          // Create a new attempt
          $attempt = StageAttempt::create([
               'user_id' => $user->id,
               'stage_id' => $stage->id,
               'total_questions' => $stage->questions()->count(),
               'started_at' => Carbon::now(),
          ]);

          // Cache question IDs for the stage to prevent heavy DB load per student attempt
          $questionIds = \Illuminate\Support\Facades\Cache::remember("stage_{$stage->id}_question_ids", 60 * 60, function () use ($stage) {
               return $stage->questions()->pluck('id')->toArray();
          });

          // Shuffle array in PHP instead of `ORDER BY RAND()` in DB
          shuffle($questionIds);

          foreach ($questionIds as $questionId) {
               AttemptAnswer::create([
                    'stage_attempt_id' => $attempt->id,
                    'question_id' => $questionId,
               ]);
          }

          return redirect()->route('quiz.show', $attempt);
     }

     /**
      * Show the quiz page with timer.
      */
     public function show(Request $request, StageAttempt $attempt)
     {
          $user = $request->user();

          // Verify ownership
          if ($attempt->user_id !== $user->id) {
               abort(403);
          }

          // If already completed, redirect to result
          if ($attempt->completed_at) {
               return redirect()->route('quiz.result', $attempt);
          }

          $stage = $attempt->stage;
          $answers = $attempt->answers()->with('question')->get();

          // Calculate remaining time
          $elapsed = (int) $attempt->started_at->diffInSeconds(now());
          $totalSeconds = $stage->time_limit_minutes * 60;
          $remainingSeconds = max(0, $totalSeconds - $elapsed);

          // Auto-submit if time has expired
          if ($remainingSeconds <= 0) {
               return $this->gradeAttempt($attempt, $user, []);
          }

          return view('quiz.show', compact('attempt', 'stage', 'answers', 'remainingSeconds'));
     }

     /**
      * Submit quiz answers.
      */
     public function submit(Request $request, StageAttempt $attempt)
     {
          $user = $request->user();

          if ($attempt->user_id !== $user->id) {
               abort(403);
          }

          if ($attempt->completed_at) {
               return redirect()->route('quiz.result', $attempt);
          }

          $submittedAnswers = $request->input('answers', []);

          return $this->gradeAttempt($attempt, $user, $submittedAnswers);
     }

     /**
      * Show quiz results.
      */
     public function result(Request $request, StageAttempt $attempt)
     {
          $user = $request->user();

          if ($attempt->user_id !== $user->id) {
               abort(403);
          }

          if (!$attempt->completed_at) {
               return redirect()->route('quiz.show', $attempt);
          }

          $attempt->load(['stage', 'answers.question']);

          return view('quiz.result', compact('attempt', 'user'));
     }

     /**
      * Grade an attempt — core scoring logic.
      */
     private function gradeAttempt(StageAttempt $attempt, $user, array $submittedAnswers)
     {
          $score = 0;
          $answers = $attempt->answers()->with('question')->get();

          foreach ($answers as $answer) {
               $selected = $submittedAnswers[$answer->question_id] ?? null;
               $isCorrect = $selected === $answer->question->correct_answer;

               $answer->update([
                    'selected_answer' => $selected,
                    'is_correct' => $isCorrect,
               ]);

               if ($isCorrect)
                    $score++;
          }

          $totalQuestions = $answers->count();
          $percentage = $totalQuestions > 0 ? ($score / $totalQuestions) * 100 : 0;
          $passed = $percentage >= $attempt->stage->passing_percentage;

          // Calculate time spent
          $timeSpent = (int) $attempt->started_at->diffInSeconds(now());

          $attempt->update([
               'score' => $score,
               'total_questions' => $totalQuestions,
               'passed' => $passed,
               'time_spent_seconds' => $timeSpent,
               'completed_at' => Carbon::now(),
          ]);

          // Award points and stars
          $this->awardGamification($attempt, $user, $passed, $percentage);

          // Update study streak
          $this->updateStreak($user);

          return redirect()->route('quiz.result', $attempt);
     }

     /**
      * Update the user's daily study streak.
      */
     private function updateStreak($user): void
     {
          $today = now()->toDateString();

          if ($user->last_activity && $user->last_activity->toDateString() === $today) {
               // Already studied today — no change
               return;
          }

          if ($user->last_activity && $user->last_activity->toDateString() === now()->subDay()->toDateString()) {
               // Studied yesterday — extend streak
               $user->streak += 1;
          } else {
               // Missed a day or first activity — reset to 1
               $user->streak = 1;
          }

          $user->last_activity = $today;
          $user->save();
     }


     /**
      * Award points, stars, and send notifications.
      */
     private function awardGamification(StageAttempt $attempt, $user, bool $passed, float $percentage)
     {
          $stage = $attempt->stage;

          if ($passed) {
               $isFirstPass = $attempt->isFirstPass();
               $points = $isFirstPass ? $stage->points_reward : intval($stage->points_reward / 2);

               $user->increment('total_points', $points);

               if ($isFirstPass) {
                    $user->increment('stars', 1);
               }

               // Perfect score bonus
               if ($percentage >= 100) {
                    $user->increment('total_points', 50);
                    $user->increment('stars', 1);
                    $user->notify(new StageCompleted($attempt, [
                         'en' => "⭐ Perfect score on {$stage->title}! +50 bonus points!",
                         'ar' => "⭐ درجة كاملة في " . ($stage->title_ar ?: $stage->title) . "! +50 نقطة إضافية!"
                    ], 'success'));
               }

               $msgEn = $isFirstPass
                    ? "🎉 You passed {$stage->title}! +{$points} points!"
                    : "Great job retrying {$stage->title}! +{$points} points!";
               
               $msgAr = $isFirstPass
                    ? "🎉 لقد اجتزت " . ($stage->title_ar ?: $stage->title) . "! +{$points} نقطة!"
                    : "عمل رائع في إعادة " . ($stage->title_ar ?: $stage->title) . "! +{$points} نقطة!";

               $user->notify(new StageCompleted($attempt, ['en' => $msgEn, 'ar' => $msgAr], 'success'));

               // Notify about next stage unlock
               $nextStage = Stage::where('order', $stage->order + 1)->first();
                if ($nextStage && $isFirstPass) {
                     $user->notify(new StageCompleted($attempt, [
                         'en' => "🔓 Stage '{$nextStage->title}' is now unlocked!",
                         'ar' => "🔓 المرحلة '" . ($nextStage->title_ar ?: $nextStage->title) . "' متاحة الآن!"
                     ], 'info'));
                }
          } else {
               $user->notify(new StageCompleted($attempt, [
                    'en' => "Keep trying! You scored {$attempt->score}/{$attempt->total_questions} on {$stage->title}. You need {$stage->passing_percentage}% to pass.",
                    'ar' => "استمر في المحاولة! لقد سجلت {$attempt->score}/{$attempt->total_questions} في " . ($stage->title_ar ?: $stage->title) . ". تحتاج إلى {$stage->passing_percentage}% للنجاح."
               ], 'warning'));
          }
     }
}
