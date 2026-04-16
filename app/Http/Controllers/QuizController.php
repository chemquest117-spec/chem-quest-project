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
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
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

            $metaTitle = 'Quiz: '.$stage->getTranslatedTitle().' — '.config('app.name');
            $metaDescription = 'Currently taking the '.$stage->getTranslatedTitle().' quiz. Good luck!';

            return view('quiz.show', compact('attempt', 'stage', 'answers', 'remainingSeconds', 'totalSeconds', 'metaTitle', 'metaDescription'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
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
                'answer' => 'required|string|max:100',
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
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
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
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
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
            $metaTitle = 'Quiz Result: '.$attempt->stage->getTranslatedTitle().' — '.config('app.name');
            $metaDescription = 'I scored '.$percentage.'% on the '.$attempt->stage->getTranslatedTitle().' quiz! Can you beat my score?';

            return view('quiz.result', compact('attempt', 'user', 'metaTitle', 'metaDescription'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while loading your quiz results. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Grade an attempt — core scoring logic.
     * Handles MCQ (exact match) and complete (numeric comparison with optional tolerance).
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
                    if ($selected !== null && !is_array($selected)) {
                        $selected = strip_tags((string) $selected);
                    }

                    if ($question->isMcq()) {
                        // MCQ: exact match (case-insensitive)
                        $isCorrect = $selected !== null && strtolower(trim($selected)) === strtolower(trim($question->correct_answer));
                    } elseif ($question->isComplete()) {
                        // For complete questions submitted as array, convert to JSON string
                        if (is_array($selected)) {
                            $selected = json_encode($selected);
                        }
                        // Complete: deterministic numeric comparison (multi-blank)
                        $isCorrect = $this->gradeComplete($selected, $question->expected_answers);
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
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while grading your quiz. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Grade a complete (fill-in-the-blank) answer using deterministic numeric comparison.
     * Supports multiple blanks — each blank's answer is compared to its expected value.
     * Returns true only if ALL blanks are correct within their tolerance.
     */
    private function gradeComplete(?string $selectedAnswer, ?array $expectedAnswers): bool
    {
        if ($selectedAnswer === null || $selectedAnswer === '' || empty($expectedAnswers)) {
            return false;
        }

        // Parse student answers — stored as JSON array (e.g. '["-2","3"]')
        $studentAnswers = json_decode($selectedAnswer, true);
        if (! is_array($studentAnswers)) {
            // Single value (backward compat or single-blank question)
            $studentAnswers = [$selectedAnswer];
        }

        // Must have same number of blanks answered
        if (count($studentAnswers) !== count($expectedAnswers)) {
            return false;
        }

        foreach ($expectedAnswers as $i => $expected) {
            $studentVal = $studentAnswers[$i] ?? null;
            if ($studentVal === null || $studentVal === '') {
                return false;
            }

            // Normalize dashes/minus signs
            $studentVal = str_replace(['−', '–', '—'], '-', trim((string) $studentVal));

            if (! is_numeric($studentVal)) {
                return false;
            }

            $expectedValue = (float) ($expected['value'] ?? 0);
            $tolerance = (float) ($expected['tolerance'] ?? 0);

            if (abs((float) $studentVal - $expectedValue) > $tolerance) {
                return false;
            }
        }

        return true;
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
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
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
                $earnedPoints = intval($stage->points_reward * ($percentage / 100));
                $points = $isFirstPass ? $earnedPoints : intval($earnedPoints / 2);

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
                        'ar' => '⭐ درجة كاملة في '.($stage->title_ar ?: $stage->title).'! +50 نقطة إضافية!',
                    ], 'success'));
                }

                $msgEn = $isFirstPass
                    ? "🎉 You passed {$stage->title}! +{$points} points!"
                    : "Great job retrying {$stage->title}! +{$points} points!";

                $msgAr = $isFirstPass
                    ? '🎉 لقد اجتزت '.($stage->title_ar ?: $stage->title)."! +{$points} نقطة!"
                    : 'عمل رائع في إعادة '.($stage->title_ar ?: $stage->title)."! +{$points} نقطة!";

                $user->notify(new StageCompleted($attempt, ['en' => $msgEn, 'ar' => $msgAr], 'success'));

                // Notify about next stage unlock
                $nextStage = Stage::where('order', $stage->order + 1)->first();
                if ($nextStage && $isFirstPass) {
                    $user->notify(new StageCompleted($attempt, [
                        'en' => "🔓 Stage '{$nextStage->title}' is now unlocked!",
                        'ar' => "🔓 المرحلة '".($nextStage->title_ar ?: $nextStage->title)."' متاحة الآن!",
                    ], 'info'));
                }
            } else {
                $user->notify(new StageCompleted($attempt, [
                    'en' => "Keep trying! You scored {$attempt->score}/{$attempt->total_questions} on {$stage->title}. You need {$stage->passing_percentage}% to pass.",
                    'ar' => "استمر في المحاولة! لقد سجلت {$attempt->score}/{$attempt->total_questions} في ".($stage->title_ar ?: $stage->title).". تحتاج إلى {$stage->passing_percentage}% للنجاح.",
                ], 'warning'));
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
