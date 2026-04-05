<?php

namespace App\Http\Controllers;

use App\Models\AttemptAnswer;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Notifications\StageCompleted;
use App\Services\ProgressSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Start a new quiz attempt for a stage.
     */
    public function start(Request $request, Stage $stage)
    {
        try {
            $user = $request->user();

            // Validate stage is unlocked
            if (! $stage->isUnlockedFor($user)) {
                return redirect()->route('stages.index')
                    ->with('error', __('messages.stage_locked'));
            }

            // Check minimum questions
            if ($stage->questions()->count() === 0) {
                return redirect()->route('stages.show', $stage)
                    ->with('error', __('messages.error'));
            }

            // Prevent concurrent quiz attempts on same stage (with DB lock to prevent race conditions)
            $attempt = DB::transaction(function () use ($user, $stage) {
                $activeAttempt = StageAttempt::where('user_id', $user->id)
                    ->where('stage_id', $stage->id)
                    ->whereNull('completed_at')
                    ->lockForUpdate()
                    ->first();

                if ($activeAttempt) {
                    return $activeAttempt;
                }

                // Create a new attempt
                $newAttempt = StageAttempt::create([
                    'user_id' => $user->id,
                    'stage_id' => $stage->id,
                    'total_questions' => $stage->questions()->count(),
                    'started_at' => Carbon::now(),
                ]);

                // Cache question IDs for the stage to prevent heavy DB load per student attempt
                $questionIds = Cache::remember("stage_{$stage->id}_question_ids", 60 * 60, function () use ($stage) {
                    return $stage->questions()->pluck('id')->toArray();
                });

                // Shuffle array in PHP instead of `ORDER BY RAND()` in DB
                shuffle($questionIds);

                foreach ($questionIds as $questionId) {
                    AttemptAnswer::create([
                        'stage_attempt_id' => $newAttempt->id,
                        'question_id' => $questionId,
                    ]);
                }

                return $newAttempt;
            });

            return redirect()->route('quiz.show', $attempt);
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while starting the quiz. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Show the quiz page with timer.
     */
    public function show(Request $request, StageAttempt $attempt)
    {
        try {
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

            $metaTitle = 'Quiz: ' . $stage->getTranslatedTitle() . ' — ' . config('app.name');
            $metaDescription = 'Currently taking the ' . $stage->getTranslatedTitle() . ' quiz. Good luck!';

            return view('quiz.show', compact('attempt', 'stage', 'answers', 'remainingSeconds', 'totalSeconds', 'metaTitle', 'metaDescription'));
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while loading the quiz. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Save a single answer via AJAX (auto-save as students select).
     */
    public function saveAnswer(Request $request, StageAttempt $attempt)
    {
        try {
            $user = $request->user();

            if ($attempt->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if ($attempt->completed_at) {
                return response()->json(['error' => 'Quiz already completed'], 422);
            }

            $request->validate([
                'question_id' => 'required|integer',
                'answer' => 'required|string|max:5000',
            ]);

            // Sanitize answer to prevent stored XSS
            $sanitizedAnswer = strip_tags($request->answer);

            $answer = $attempt->answers()
                ->where('question_id', $request->question_id)
                ->first();

            if ($answer) {
                /** @var AttemptAnswer $answer */
                $answer->update(['selected_answer' => $sanitizedAnswer]);

                return response()->json(['success' => true]);
            }

            return response()->json(['error' => 'Answer not found'], 404);
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while saving your answer. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Submit quiz answers.
     */
    public function submit(Request $request, StageAttempt $attempt)
    {
        try {
            $user = $request->user();

            if ($attempt->user_id !== $user->id) {
                abort(403);
            }

            if ($attempt->completed_at) {
                return redirect()->route('quiz.result', $attempt);
            }

            $submittedAnswers = $request->input('answers', []);

            return $this->gradeAttempt($attempt, $user, $submittedAnswers);
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while submitting your quiz. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Show quiz results.
     */
    public function result(Request $request, StageAttempt $attempt)
    {
        try {
            $user = $request->user();

            if ($attempt->user_id !== $user->id) {
                abort(403);
            }

            if (! $attempt->completed_at) {
                return redirect()->route('quiz.show', $attempt);
            }

            // Prevent cheating by viewing past results while a new attempt is active
            $hasActiveAttempt = StageAttempt::where('user_id', $user->id)
                ->where('stage_id', $attempt->stage_id)
                ->whereNull('completed_at')
                ->exists();

            if ($hasActiveAttempt) {
                return redirect()->route('stages.index')
                    ->with('error', 'You cannot view old results while taking an active quiz.');
            }

            $attempt->load(['stage', 'answers.question']);

            $percentage = $attempt->total_questions > 0 ? round(($attempt->score / $attempt->total_questions) * 100) : 0;
            $metaTitle = 'Quiz Result: ' . $attempt->stage->getTranslatedTitle() . ' — ' . config('app.name');
            $metaDescription = 'I scored ' . $percentage . '% on the ' . $attempt->stage->getTranslatedTitle() . ' quiz! Can you beat my score?';

            return view('quiz.result', compact('attempt', 'user', 'metaTitle', 'metaDescription'));
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while loading your quiz results. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Grade an attempt — core scoring logic.
     * Handles both MCQ (exact match) and essay (keyword-based partial match).
     */
    private function gradeAttempt(StageAttempt $attempt, $user, array $submittedAnswers)
    {
        try {
            return DB::transaction(function () use ($attempt, $user, $submittedAnswers) {
                $score = 0;
                $answers = $attempt->answers()->with('question')->get();

                foreach ($answers as $answer) {
                    $question = $answer->question;
                    $selected = $submittedAnswers[$answer->question_id] ?? $answer->selected_answer;

                    // Sanitize submitted answers
                    if ($selected !== null) {
                        $selected = strip_tags($selected);
                    }

                    if ($question->isMcq()) {
                        // MCQ: exact match (case-insensitive)
                        $isCorrect = $selected !== null && strtolower(trim($selected)) === strtolower(trim($question->correct_answer));
                    } elseif ($question->isEssay()) {
                        // Essay: keyword-based scoring against expected_answer
                        $isCorrect = $this->gradeEssay($selected, $question->expected_answer);
                    } else {
                        $isCorrect = false;
                    }

                    $answer->update([
                        'selected_answer' => $selected,
                        'is_correct' => $isCorrect,
                    ]);

                    if ($isCorrect) {
                        $score++;
                    }
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

                // Invalidate user dashboard caches so front-end reflects new stats immediately
                Cache::forget("user_{$user->id}_dashboard_stats");
                Cache::forget("user_{$user->id}_recent_attempts");

                // Sync with study planner (auto-mark items as completed)
                if ($passed) {
                    app(ProgressSyncService::class)->syncFromAttempt($attempt);
                }

                return redirect()->route('quiz.result', $attempt);
            });
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while grading your quiz. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Grade an essay answer using keyword matching.
     * Returns true if the student's answer contains enough key terms.
     */
    private function gradeEssay(?string $studentAnswer, ?string $expectedAnswer): bool
    {
        try {
            if (empty($studentAnswer) || empty($expectedAnswer)) {
                return false;
            }

            // Normalize dashes/minus signs to standard hyphen for comparison
            $studentAnswer = str_replace(['−', '–', '—'], '-', $studentAnswer);
            $expectedAnswer = str_replace(['−', '–', '—'], '-', $expectedAnswer);

            $studentAnswer = strtolower(trim($studentAnswer));
            $expectedAnswer = strtolower(trim($expectedAnswer));

            $studentNoSpace = str_replace(' ', '', $studentAnswer);
            $expectedNoSpace = str_replace(' ', '', $expectedAnswer);

            // Exact match (ignoring spaces)
            if ($studentNoSpace === $expectedNoSpace) {
                return true;
            }

            // If expected answer is a short phrase, equation, or number (<15 chars without spaces)
            if (mb_strlen($expectedNoSpace) < 15) {
                return str_contains($studentNoSpace, $expectedNoSpace);
            }

            // For longer essay answers, extract keywords (ignore stop words and 1-char words)
            $stopWords = ['the', 'is', 'at', 'which', 'on', 'and', 'a', 'an', 'of', 'in', 'to', 'with', 'for', 'it', 'as', 'by', 'are', 'be', 'this', 'that'];
            $expectedWords = array_filter(
                preg_split('/[\s,;.]+/', $expectedAnswer),
                fn($word) => mb_strlen($word) > 1 && ! in_array($word, $stopWords)
            );

            if (empty($expectedWords)) {
                // Fallback: direct similarity check with a much higher threshold than 50%
                similar_text($studentAnswer, $expectedAnswer, $percent);

                return $percent >= 85;
            }

            // Count how many expected keywords appear in student answer
            $matchCount = 0;
            foreach ($expectedWords as $word) {
                if (str_contains($studentAnswer, $word)) {
                    $matchCount++;
                }
            }

            // Student must match at least 65% of keywords for essays
            $matchRatio = $matchCount / count($expectedWords);

            return $matchRatio >= 0.65;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    /**
     * Update the user's daily study streak.
     */
    private function updateStreak($user): void
    {
        try {
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
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Award points, stars, and send notifications.
     */
    private function awardGamification(StageAttempt $attempt, $user, bool $passed, float $percentage)
    {
        try {
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
                        'ar' => '⭐ درجة كاملة في ' . ($stage->title_ar ?: $stage->title) . '! +50 نقطة إضافية!',
                    ], 'success'));
                }

                $msgEn = $isFirstPass
                    ? "🎉 You passed {$stage->title}! +{$points} points!"
                    : "Great job retrying {$stage->title}! +{$points} points!";

                $msgAr = $isFirstPass
                    ? '🎉 لقد اجتزت ' . ($stage->title_ar ?: $stage->title) . "! +{$points} نقطة!"
                    : 'عمل رائع في إعادة ' . ($stage->title_ar ?: $stage->title) . "! +{$points} نقطة!";

                $user->notify(new StageCompleted($attempt, ['en' => $msgEn, 'ar' => $msgAr], 'success'));

                // Notify about next stage unlock
                $nextStage = Stage::where('order', $stage->order + 1)->first();
                if ($nextStage && $isFirstPass) {
                    $user->notify(new StageCompleted($attempt, [
                        'en' => "🔓 Stage '{$nextStage->title}' is now unlocked!",
                        'ar' => "🔓 المرحلة '" . ($nextStage->title_ar ?: $nextStage->title) . "' متاحة الآن!",
                    ], 'info'));
                }
            } else {
                $user->notify(new StageCompleted($attempt, [
                    'en' => "Keep trying! You scored {$attempt->score}/{$attempt->total_questions} on {$stage->title}. You need {$stage->passing_percentage}% to pass.",
                    'ar' => "استمر في المحاولة! لقد سجلت {$attempt->score}/{$attempt->total_questions} في " . ($stage->title_ar ?: $stage->title) . ". تحتاج إلى {$stage->passing_percentage}% للنجاح.",
                ], 'warning'));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
