<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Casts\PostgresBoolean;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminDashboardController extends Controller
{
    public function index()
    {
        try {
            $totalStudents = User::student()->count();
            $activeStudents = User::student()->where('is_banned', PostgresBoolean::asQueryValue(false))->count();
            $blockedStudents = User::student()->where('is_banned', PostgresBoolean::asQueryValue(true))->count();
            $totalAdmins = User::whereIn('role', ['admin', 'super_admin'])->count();
            $totalStages = Stage::count();
            $totalAttempts = StageAttempt::count();
            $passRate = StageAttempt::count() > 0
                ? round(StageAttempt::passed()->count() / StageAttempt::count() * 100, 1)
                : 0;

            $recentAttempts = StageAttempt::with(['user', 'stage'])
                ->latest()
                ->take(10)
                ->get();

            $recentAuditLogs = AuditLog::with('user')
                ->latest()
                ->take(10)
                ->get();

            return view('admin.dashboard', compact(
                'totalStudents',
                'activeStudents',
                'blockedStudents',
                'totalAdmins',
                'totalStages',
                'totalAttempts',
                'passRate',
                'recentAttempts',
                'recentAuditLogs'
            ));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            session()->now('error', __('admin.we_encountered_an_unexpected_f602'));

            return view('admin.dashboard', [
                'totalStudents' => 0,
                'activeStudents' => 0,
                'blockedStudents' => 0,
                'totalAdmins' => 0,
                'totalStages' => 0,
                'totalAttempts' => 0,
                'passRate' => 0,
                'recentAttempts' => collect(),
                'recentAuditLogs' => collect(),
            ]);
        }
    }
}
