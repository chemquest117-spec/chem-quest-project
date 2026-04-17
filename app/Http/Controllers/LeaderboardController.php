<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\CacheTTL;
use App\Support\TwoLayerCache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LeaderboardController extends Controller
{
    public function index()
    {
        try {
            $students = TwoLayerCache::remember(
                'leaderboard_data',
                CacheTTL::DYNAMIC_REDIS,
                CacheTTL::DYNAMIC_MEMORY,
                fn () => User::student()
                    ->orderByDesc('total_points')
                    ->orderByDesc('stars')
                    ->take(50)
                    ->get(),
                CacheTTL::DYNAMIC_STALE,
            );

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
                ->with('error', __('leaderboard.we_encountered_an_unexpected_db9c'));
        }
    }

    public function data()
    {
        try {
            $students = TwoLayerCache::remember(
                'leaderboard_json_data',
                CacheTTL::DYNAMIC_REDIS,
                CacheTTL::DYNAMIC_MEMORY,
                fn () => User::student()
                    ->orderByDesc('total_points')
                    ->orderByDesc('stars')
                    ->take(50)
                    ->get(['id', 'name', 'total_points', 'stars', 'streak']),
                CacheTTL::DYNAMIC_STALE,
            );

            return response()->json($students);
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', __('leaderboard.we_encountered_an_unexpected_3645'));
        }
    }
}
