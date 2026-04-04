<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{
    public function index()
    {
        $students = Cache::remember('leaderboard_data', 3600, function () {
            return User::where('is_admin', false)
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

        $metaTitle = "Leaderboard — " . config('app.name');
        $metaDescription = "Check out the top students on " . config('app.name') . "! See who is leading in points and stars.";

        return view('leaderboard.index', compact('students', 'studentsJson', 'metaTitle', 'metaDescription'));
    }

    public function data()
    {
        $students = Cache::remember('leaderboard_json_data', 3600, function () {
            return User::where('is_admin', false)
                ->orderByDesc('total_points')
                ->orderByDesc('stars')
                ->take(50)
                ->get(['id', 'name', 'total_points', 'stars', 'streak']);
        });

        return response()->json($students);
    }
}
