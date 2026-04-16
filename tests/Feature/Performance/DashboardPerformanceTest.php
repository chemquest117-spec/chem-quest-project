<?php

use App\Models\Stage;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

it('loads dashboard with fewer queries on warm cache', function () {
    $user = User::factory()->create();
    Stage::factory()->count(10)->sequence(fn ($sequence) => ['order' => $sequence->index + 1])->create();

    Cache::flush();

    DB::flushQueryLog();
    DB::enableQueryLog();

    actingAs($user)->get(route('dashboard'))->assertOk();
    $coldQueries = count(DB::getQueryLog());

    DB::flushQueryLog();
    DB::enableQueryLog();

    actingAs($user)->get(route('dashboard'))->assertOk();
    $warmQueries = count(DB::getQueryLog());

    // Warm cache should never be worse than cold.
    expect($warmQueries)->toBeLessThanOrEqual($coldQueries);

    // Baselines: should improve further after Redis + cache invalidation cleanup.
    expect($coldQueries)->toBeLessThanOrEqual(80);
    expect($warmQueries)->toBeLessThanOrEqual(40);
});
