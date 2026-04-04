<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

use function Pest\Laravel\actingAs;

it('dashboard prevents n+1 queries via strict model enforcement', function () {
    // We enforce strictly preventing lazy loading in this test
    Model::preventLazyLoading();

    $user = User::factory()->create();

    // Just load the dashboard route, and if there are lazy loading queries,
    // Laravel will throw a LazyLoadingViolationException.
    // That means if it passes, it is efficient.
    actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});
