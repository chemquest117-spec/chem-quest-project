<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminStageController;
use App\Http\Controllers\Admin\AdminQuestionController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

/*
|--------------------------------------------------------------------------
| Language Route
|--------------------------------------------------------------------------
*/

Route::get('/language/{locale}', function ($locale) {
    if (!in_array($locale, ['en', 'ar'])) {
        abort(400);
    }
    session()->put('locale', $locale);
    return redirect()->back();
})->name('language.switch');

/*
|--------------------------------------------------------------------------
| Authenticated Student Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Stages
    Route::get('/stages', [StageController::class, 'index'])->name('stages.index');
    Route::get('/stages/{stage}', [StageController::class, 'show'])->name('stages.show');

    // Quiz
    Route::post('/stages/{stage}/quiz/start', [QuizController::class, 'start'])->name('quiz.start');
    Route::get('/quiz/{attempt}', [QuizController::class, 'show'])->name('quiz.show');
    Route::post('/quiz/{attempt}/save-answer', [QuizController::class, 'saveAnswer'])->name('quiz.saveAnswer');
    Route::post('/quiz/{attempt}/submit', [QuizController::class, 'submit'])->name('quiz.submit');
    Route::get('/quiz/{attempt}/result', [QuizController::class, 'result'])->name('quiz.result');

    // Leaderboard
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
    Route::get('/leaderboard/data', [LeaderboardController::class, 'data'])->name('leaderboard.data');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('stages', AdminStageController::class)->except(['show']);
    Route::resource('stages.questions', AdminQuestionController::class)->except(['show']);

    // AI Question Generation
    Route::post('/stages/{stage}/questions/generate', [AdminQuestionController::class, 'generate'])->name('stages.questions.generate');

    // Analytics Dashboard
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics');

    // Student Management
    Route::get('/students', [AdminStudentController::class, 'index'])->name('students.index');
    Route::get('/students/{user}', [AdminStudentController::class, 'show'])->name('students.show');
    Route::delete('/students/{user}', [AdminStudentController::class, 'destroy'])->name('students.destroy');
    Route::post('/students/{user}/toggle-ban', [AdminStudentController::class, 'toggleBan'])->name('students.toggleBan');
    Route::post('/students/{user}/reset-password', [AdminStudentController::class, 'resetPassword'])->name('students.resetPassword');
});

/*
|--------------------------------------------------------------------------
| System Fallback
|--------------------------------------------------------------------------
*/

Route::get('/sys-suspended', function () {
    return view('errors.sys-suspended');
})->name('sys.suspended');

require __DIR__ . '/auth.php';
