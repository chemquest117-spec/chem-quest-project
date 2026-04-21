<?php

use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use App\Notifications\MotivationalNotification;
use App\Services\MotivationService;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->service = new MotivationService;
});

// ── Post-Quiz Notifications ────────────────────────────────────────

it('sends success motivation when quiz is passed', function () {
    $user = User::factory()->create(['streak' => 1]);
    $stage = Stage::factory()->create(['passing_percentage' => 75]);
    $attempt = StageAttempt::factory()->create([
        'user_id' => $user->id,
        'stage_id' => $stage->id,
        'score' => 8,
        'total_questions' => 10,
        'passed' => true,
        'completed_at' => now(),
    ]);

    $this->service->afterQuizCompletion($attempt, $user);

    Notification::assertSentTo($user, MotivationalNotification::class, function ($notification) {
        return $notification->category === 'success';
    });
});

it('sends failure encouragement when quiz is failed', function () {
    $user = User::factory()->create(['streak' => 0]);
    $stage = Stage::factory()->create(['passing_percentage' => 75]);
    $attempt = StageAttempt::factory()->create([
        'user_id' => $user->id,
        'stage_id' => $stage->id,
        'score' => 3,
        'total_questions' => 10,
        'passed' => false,
        'completed_at' => now(),
    ]);

    $this->service->afterQuizCompletion($attempt, $user);

    Notification::assertSentTo($user, MotivationalNotification::class, function ($notification) {
        return $notification->category === 'failure';
    });
});

it('includes dynamic data in success notification metadata', function () {
    $user = User::factory()->create(['streak' => 5]);
    $stage = Stage::factory()->create();
    $attempt = StageAttempt::factory()->create([
        'user_id' => $user->id,
        'stage_id' => $stage->id,
        'score' => 9,
        'total_questions' => 10,
        'passed' => true,
        'completed_at' => now(),
    ]);

    $this->service->afterQuizCompletion($attempt, $user);

    Notification::assertSentTo($user, MotivationalNotification::class, function ($notification) use ($attempt) {
        return $notification->metadata['score'] === 9
            && $notification->metadata['total'] === 10
            && $notification->metadata['streak'] === 5
            && $notification->metadata['attempt_id'] === $attempt->id;
    });
});

// ── Streak Milestone Notifications ─────────────────────────────────

it('sends streak milestone notification at 3-day streak', function () {
    $user = User::factory()->create(['streak' => 3]);
    $stage = Stage::factory()->create();
    $attempt = StageAttempt::factory()->create([
        'user_id' => $user->id,
        'stage_id' => $stage->id,
        'score' => 8,
        'total_questions' => 10,
        'passed' => true,
        'completed_at' => now(),
    ]);

    $this->service->afterQuizCompletion($attempt, $user);

    Notification::assertSentTo($user, MotivationalNotification::class, function ($notification) {
        return $notification->category === 'streak'
            && $notification->metadata['streak'] === 3;
    });
});

it('does not send streak notification at non-milestone counts', function () {
    $user = User::factory()->create(['streak' => 5]);
    $stage = Stage::factory()->create();
    $attempt = StageAttempt::factory()->create([
        'user_id' => $user->id,
        'stage_id' => $stage->id,
        'score' => 8,
        'total_questions' => 10,
        'passed' => true,
        'completed_at' => now(),
    ]);

    $this->service->afterQuizCompletion($attempt, $user);

    // Should get success but NOT streak
    Notification::assertSentTo($user, MotivationalNotification::class, function ($notification) {
        return $notification->category === 'success';
    });

    Notification::assertNotSentTo($user, MotivationalNotification::class, function ($notification) {
        return $notification->category === 'streak';
    });
});

// ── Level Up Notification ──────────────────────────────────────────

it('sends level-up notification when 1 stage remaining', function () {
    // Create 3 stages
    $stages = Stage::factory()->count(3)->create();

    // User has passed first 2 stages (1 remaining)
    $user = User::factory()->create();
    StageAttempt::factory()->create([
        'user_id' => $user->id,
        'stage_id' => $stages[0]->id,
        'passed' => true,
        'completed_at' => now(),
    ]);
    StageAttempt::factory()->create([
        'user_id' => $user->id,
        'stage_id' => $stages[1]->id,
        'passed' => true,
        'completed_at' => now(),
    ]);

    // Failed attempt on stage 3 — triggers afterQuizCompletion but keeps 1 stage remaining
    $attempt = StageAttempt::factory()->create([
        'user_id' => $user->id,
        'stage_id' => $stages[2]->id,
        'score' => 3,
        'total_questions' => 10,
        'passed' => false,
        'completed_at' => now(),
    ]);

    // Reset user's memoized cache
    $user = $user->fresh();

    $this->service->afterQuizCompletion($attempt, $user);

    Notification::assertSentTo($user, MotivationalNotification::class, function ($notification) {
        return $notification->category === 'level_up';
    });
});

// ── Comeback Notifications ─────────────────────────────────────────

it('sends comeback notification to inactive students', function () {
    $inactiveUser = User::factory()->create([
        'last_activity' => now()->subDays(5),
        'role' => 'student',
    ]);

    $count = $this->service->sendComebackNotifications();

    expect($count)->toBe(1);
    Notification::assertSentTo($inactiveUser, MotivationalNotification::class, function ($notification) {
        return $notification->category === 'comeback';
    });
});

it('does not send comeback to recently active students', function () {
    User::factory()->create([
        'last_activity' => now(),
        'role' => 'student',
    ]);

    $count = $this->service->sendComebackNotifications();

    expect($count)->toBe(0);
});

it('does not send comeback to admin users', function () {
    User::factory()->create([
        'last_activity' => now()->subDays(5),
        'role' => 'admin',
    ]);

    $count = $this->service->sendComebackNotifications();

    expect($count)->toBe(0);
});

// ── Batch Streak Milestone Notifications ───────────────────────────

it('sends streak milestone notifications in batch', function () {
    $user = User::factory()->create([
        'streak' => 7,
        'role' => 'student',
    ]);

    $count = $this->service->sendStreakMilestoneNotifications();

    expect($count)->toBe(1);
    Notification::assertSentTo($user, MotivationalNotification::class, function ($notification) {
        return $notification->category === 'streak'
            && $notification->metadata['streak'] === 7;
    });
});

// ── Notification Content ───────────────────────────────────────────

it('generates bilingual notification content', function () {
    $user = User::factory()->create(['streak' => 1]);
    $stage = Stage::factory()->create(['title' => 'Atomic Structure']);
    $attempt = StageAttempt::factory()->create([
        'user_id' => $user->id,
        'stage_id' => $stage->id,
        'score' => 8,
        'total_questions' => 10,
        'passed' => true,
        'completed_at' => now(),
    ]);

    $this->service->afterQuizCompletion($attempt, $user);

    Notification::assertSentTo($user, MotivationalNotification::class, function ($notification) {
        return str_contains($notification->messageEn, 'Atomic Structure')
            && ! empty($notification->messageAr);
    });
});
