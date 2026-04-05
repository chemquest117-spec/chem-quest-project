<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            $totalStudents = User::where('is_admin', false)->count();
            $totalStages = Stage::count();
            $totalAttempts = StageAttempt::count();
            $passRate = StageAttempt::count() > 0
                ? round(StageAttempt::where('passed', true)->count() / StageAttempt::count() * 100, 1)
                : 0;

            $recentAttempts = StageAttempt::with(['user', 'stage'])
                ->latest()
                ->take(10)
                ->get();

            return view('admin.dashboard', compact(
                'totalStudents',
                'totalStages',
                'totalAttempts',
                'passRate',
                'recentAttempts'
            ));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while loading dashboard. Please try again, or contact support if the problem persists.');
        }
    }
}
