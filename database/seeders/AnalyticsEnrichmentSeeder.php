<?php

namespace Database\Seeders;

use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class AnalyticsEnrichmentSeeder extends Seeder
{
    public function run(): void
    {
        $stages = Stage::with('questions')->orderBy('order')->get();
        if ($stages->isEmpty()) {
            $this->command?->warn('AnalyticsEnrichmentSeeder skipped: no stages found.');

            return;
        }

        $students = $this->ensureStudents();
        if ($students->isEmpty()) {
            $this->command?->warn('AnalyticsEnrichmentSeeder skipped: no students found.');

            return;
        }

        $problematicQuestionIds = Question::query()
            ->whereIn('difficulty', ['medium', 'hard'])
            ->inRandomOrder()
            ->take(10)
            ->pluck('id')
            ->all();

        if (count($problematicQuestionIds) < 5) {
            $problematicQuestionIds = Question::query()
                ->inRandomOrder()
                ->take(10)
                ->pluck('id')
                ->all();
        }

        $this->seedStageAttemptsData($students, $stages, $problematicQuestionIds);
        $this->seedDailyActivityData($students, $stages, $problematicQuestionIds);
        $this->seedStagePassData($students, $stages);
        $this->seedProblematicQuestionsData($students, $stages, $problematicQuestionIds);

        $this->command?->info('Analytics enrichment data seeded successfully.');
    }

    private function ensureStudents(): Collection
    {
        $currentStudents = User::student()->get();
        $targetStudentCount = 28;
        $missingCount = max(0, $targetStudentCount - $currentStudents->count());

        if ($missingCount > 0) {
            User::factory()
                ->count($missingCount)
                ->create()
                ->each(function (User $student): void {
                    $student->forceFill([
                        'is_admin' => false,
                        'is_banned' => false,
                        'total_points' => random_int(120, 3200),
                        'stars' => random_int(2, 90),
                        'streak' => random_int(0, 25),
                        'last_activity' => now()->subDays(random_int(0, 7)),
                    ])->save();
                });
        }

        return User::student()->get();
    }

    private function seedStudentAttempts(User $student, Collection $stages, array $problematicQuestionIds): void
    {
        $skillFactor = random_int(-12, 18) / 100; // -0.12 .. +0.18
        $attemptsCount = random_int(10, 22);
        $passedAttempts = 0;
        $earnedPoints = 0;

        for ($i = 0; $i < $attemptsCount; $i++) {
            /** @var Stage $stage */
            $stage = $stages->random();
            $startedAt = Carbon::now()
                ->subDays(random_int(0, 29))
                ->subMinutes(random_int(0, 1200));

            $baseAccuracy = 0.50 + $skillFactor - (($stage->order - 1) * 0.03);
            $baseAccuracy = max(0.20, min(0.92, $baseAccuracy));

            $attempt = $this->createAttempt(
                student: $student,
                stage: $stage,
                startedAt: $startedAt,
                baseAccuracy: $baseAccuracy,
                problematicQuestionIds: $problematicQuestionIds,
                forceInProgressChance: 8
            );
            if (! $attempt) {
                continue;
            }

            if ($attempt->passed) {
                $passedAttempts++;
                $earnedPoints += (int) round($stage->points_reward * (0.7 + ($attempt->score / max(1, $attempt->total_questions)) * 0.6));
            } else {
                $earnedPoints += (int) round($stage->points_reward * 0.18);
            }
        }

        $student->forceFill([
            'total_points' => max((int) $student->total_points, $earnedPoints + random_int(20, 250)),
            'stars' => max((int) $student->stars, min(300, $passedAttempts * random_int(1, 3))),
            'streak' => max((int) $student->streak, random_int(0, 14)),
            'last_activity' => now()->subDays(random_int(0, 4)),
        ])->save();
    }

    public function seedStageAttemptsData(Collection $students, Collection $stages, array $problematicQuestionIds): void
    {
        foreach ($students as $student) {
            if (! $student instanceof User) {
                continue;
            }

            $this->seedStudentAttempts($student, $stages, $problematicQuestionIds);
        }
    }

    public function seedDailyActivityData(Collection $students, Collection $stages, array $problematicQuestionIds): void
    {
        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $attemptsToday = random_int(4, 16);

            for ($i = 0; $i < $attemptsToday; $i++) {
                $student = $students->random();
                $stage = $stages->random();
                $startedAt = Carbon::now()->subDays($daysAgo)->startOfDay()->addMinutes(random_int(0, 1439));
                $baseAccuracy = max(0.25, min(0.9, 0.55 - (($stage->order - 1) * 0.03) + (random_int(-8, 8) / 100)));

                $this->createAttempt($student, $stage, $startedAt, $baseAccuracy, $problematicQuestionIds, 5);
            }
        }
    }

    public function seedStagePassData(Collection $students, Collection $stages): void
    {
        foreach ($stages as $stage) {
            if (! $stage instanceof Stage) {
                continue;
            }

            $targetPassRate = match ((int) $stage->order) {
                1 => 0.80,
                2 => 0.72,
                3 => 0.63,
                4 => 0.56,
                default => 0.50,
            };

            for ($i = 0; $i < 18; $i++) {
                $student = $students->random();
                $startedAt = Carbon::now()->subDays(random_int(2, 25))->subMinutes(random_int(0, 800));
                $baseAccuracy = max(0.15, min(0.95, $targetPassRate + (random_int(-10, 10) / 100)));

                $this->createAttempt($student, $stage, $startedAt, $baseAccuracy, [], 0);
            }
        }
    }

    public function seedProblematicQuestionsData(Collection $students, Collection $stages, array $problematicQuestionIds): void
    {
        if (empty($problematicQuestionIds)) {
            return;
        }

        foreach ($problematicQuestionIds as $questionId) {
            $question = Question::find($questionId);
            if (! $question || ! $question->stage) {
                continue;
            }

            for ($i = 0; $i < 12; $i++) {
                $student = $students->random();
                $startedAt = Carbon::now()->subDays(random_int(0, 20))->subMinutes(random_int(0, 900));
                $this->createAttempt(
                    student: $student,
                    stage: $question->stage,
                    startedAt: $startedAt,
                    baseAccuracy: 0.28,
                    problematicQuestionIds: [$question->id],
                    forceInProgressChance: 0
                );
            }
        }
    }

    private function createAttempt(
        User $student,
        Stage $stage,
        Carbon $startedAt,
        float $baseAccuracy,
        array $problematicQuestionIds = [],
        int $forceInProgressChance = 0
    ): ?StageAttempt {
        $questions = $stage->questions->shuffle();
        if ($questions->isEmpty()) {
            return null;
        }

        $quizQuestions = $questions->take(min(5, $questions->count()));
        $totalQuestions = $quizQuestions->count();
        if ($totalQuestions === 0) {
            return null;
        }

        $timeSpentSeconds = random_int(90, max(180, $stage->time_limit_minutes * 60 + 400));
        $correctCount = 0;
        $answerRows = [];

        foreach ($quizQuestions as $question) {
            $questionPenalty = in_array($question->id, $problematicQuestionIds, true) ? 0.28 : 0.0;
            $effectiveAccuracy = max(0.08, min(0.96, $baseAccuracy - $questionPenalty));
            $isCorrect = (mt_rand(1, 1000) / 1000) <= $effectiveAccuracy;

            if ($isCorrect) {
                $correctCount++;
            }

            $answerRows[] = [
                'question' => $question,
                'selected_answer' => $this->resolveSelectedAnswer($question, $isCorrect),
                'is_correct' => $isCorrect,
            ];
        }

        $passingScore = (int) ceil(($stage->passing_percentage / 100) * $totalQuestions);
        $passed = $correctCount >= $passingScore;
        $completedAt = (clone $startedAt)->addSeconds($timeSpentSeconds);

        $attempt = new StageAttempt;
        $attempt->forceFill([
            'user_id' => $student->id,
            'stage_id' => $stage->id,
            'score' => $correctCount,
            'total_questions' => $totalQuestions,
            'passed' => $passed,
            'time_spent_seconds' => $timeSpentSeconds,
            'started_at' => $startedAt,
            'completed_at' => random_int(1, 100) <= $forceInProgressChance ? null : $completedAt,
            'created_at' => $startedAt,
            'updated_at' => $completedAt,
        ]);
        $attempt->save();

        foreach ($answerRows as $row) {
            $answer = new AttemptAnswer;
            $answer->forceFill([
                'stage_attempt_id' => $attempt->id,
                'question_id' => $row['question']->id,
                'selected_answer' => $row['selected_answer'],
                'is_correct' => $row['is_correct'],
                'created_at' => $startedAt,
                'updated_at' => $completedAt,
            ]);
            $answer->save();
        }

        return $attempt;
    }

    private function resolveSelectedAnswer(Question $question, bool $isCorrect): ?string
    {
        if (! $question->isMcq()) {
            return null;
        }

        $options = ['a', 'b', 'c', 'd'];
        $correct = strtolower((string) ($question->correct_answer ?? 'a'));
        if (! in_array($correct, $options, true)) {
            $correct = 'a';
        }

        if ($isCorrect) {
            return $correct;
        }

        $wrongOptions = array_values(array_filter($options, fn (string $option) => $option !== $correct));

        return $wrongOptions[array_rand($wrongOptions)] ?? 'b';
    }
}
