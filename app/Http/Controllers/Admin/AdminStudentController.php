<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminStudentController extends Controller
{
    public function index()
    {
        try {
            $students = User::student()
                ->withCount('attempts')
                ->orderByDesc('total_points')
                ->paginate(20);

            $stages = Stage::orderBy('order')->get();

            return view('admin.students.index', compact('students', 'stages'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            session()->now('error', 'We encountered an unexpected error while loading the students list. Please try again, or contact support if the problem persists.');

            return view('admin.students.index', [
                'students' => new LengthAwarePaginator([], 0, 20),
                'stages' => collect(),
            ]);
        }
    }

    /**
     * Show detailed student profile with full learning journey.
     */
    public function show(User $user)
    {
        try {
            if ($user->is_admin) {
                abort(404);
            }

            $stages = Stage::orderBy('order')->get();
            $completedIds = $user->completedStageIds();

            $stageAggregates = StageAttempt::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->selectRaw('stage_id,
                    count(*) as attempts,
                    avg((score * 100.0) / nullif(total_questions, 0)) as avg_score_pct,
                    max((score * 100.0) / nullif(total_questions, 0)) as best_score_pct,
                    avg(time_spent_seconds) as avg_time')
                ->groupBy('stage_id')
                ->get()
                ->keyBy('stage_id');

            // Performance over time (last 30 attempts)
            $recentAttempts = StageAttempt::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->orderByDesc('completed_at')
                ->take(30)
                ->get();

            // Calculate strengths and weaknesses by stage
            $stagePerformance = [];
            foreach ($stages as $stage) {
                $agg = $stageAggregates->get($stage->id);

                if (! $agg) {
                    $stagePerformance[] = [
                        'stage' => $stage,
                        'attempts' => 0,
                        'avg_score' => 0,
                        'best_score' => 0,
                        'passed' => false,
                        'avg_time' => 0,
                    ];

                    continue;
                }

                $stagePerformance[] = [
                    'stage' => $stage,
                    'attempts' => (int) $agg->attempts,
                    'avg_score' => round((float) ($agg->avg_score_pct ?? 0), 1),
                    'best_score' => round((float) ($agg->best_score_pct ?? 0), 1),
                    'passed' => in_array($stage->id, $completedIds),
                    'avg_time' => round((float) ($agg->avg_time ?? 0)),
                ];
            }

            // Overall stats
            $totalAttempts = $recentAttempts->count();
            $passedAttempts = $recentAttempts->passed()->count();
            $successRate = $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100, 1) : 0;
            $totalTimeSpent = StageAttempt::where('user_id', $user->id)->sum('time_spent_seconds');

            return view('admin.students.show', compact(
                'user',
                'stages',
                'completedIds',
                'stagePerformance',
                'recentAttempts',
                'successRate',
                'totalTimeSpent'
            ));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return redirect()->route('admin.students.index')
                ->with('error', 'We encountered an unexpected error while loading student profile. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Delete a student account.
     */
    public function destroy(User $user)
    {
        try {
            if ($user->is_admin) {
                return back()->with('error', 'Cannot delete admin accounts.');
            }

            $user->delete();

            return redirect()->route('admin.students.index')
                ->with('success', "Student '{$user->name}' has been deleted.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while deleting the student. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Toggle student ban status.
     */
    public function toggleBan(User $user)
    {
        try {
            if ($user->is_admin) {
                return back()->with('error', 'Cannot ban admin accounts.');
            }

            $user->is_banned = ! ($user->is_banned ?? false);
            $user->save();

            $status = $user->is_banned ? 'banned' : 'unbanned';

            return back()->with('success', "Student '{$user->name}' has been {$status}.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while updating the student status. Please try again, or contact support if the problem persists.');
        }
    }

    /**
     * Reset student password to a random one.
     */
    public function resetPassword(User $user)
    {
        try {
            if ($user->is_admin) {
                return back()->with('error', 'Cannot reset admin passwords from here.');
            }

            $newPassword = Str::random(10);
            $user->password = Hash::make($newPassword);
            $user->save();

            return back()
                ->with('success', "Password for '{$user->name}' has been reset successfully.")
                ->with('temp_password', $newPassword);
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while resetting the password. Please try again, or contact support if the problem persists.');
        }
    }
}
