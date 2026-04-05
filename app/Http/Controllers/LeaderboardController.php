<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LeaderboardController extends Controller
{
    public function index()
    {
        try {
            $students = Cache::remember('leaderboard_data', 3600, function () {
                return User::student()
                    ->orderByDesc('total_points')
                    ->orderByDesc('stars')
                    ->take(50)
                    ->get();
            });

            $studentsJson = $students->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'total_points' => $s->total_points,
                    'stars' => $s->stars,
                    'streak' => $s->streak ?? 0,
                ];
            });

            $metaTitle = 'Leaderboard — '.config('app.name');
            $metaDescription = 'Check out the top students on '.config('app.name').'! See who is leading in points and stars.';

            return view('leaderboard.index', compact('students', 'studentsJson', 'metaTitle', 'metaDescription'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while loading the leaderboard. Please try again, or contact support if the problem persists.');
        }
    }

    public function data()
    {
        try {
            $students = Cache::remember('leaderboard_json_data', 3600, function () {
                return User::student()
                    ->orderByDesc('total_points')
                    ->orderByDesc('stars')
                    ->take(50)
                    ->get(['id', 'name', 'total_points', 'stars', 'streak']);
            });

            return response()->json($students);
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while loading the leaderboard data. Please try again, or contact support if the problem persists.');
        }
    }
}
