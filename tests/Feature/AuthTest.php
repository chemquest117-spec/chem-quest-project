<?php

use App\Models\User;
use function Pest\Laravel\{post, assertAuthenticatedAs, assertGuest};

it('users can register', function () {
    post('/register', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
});

it('users can login', function () {
    $user = User::factory()->create([
        'password' => \Illuminate\Support\Facades\Hash::make('password')
    ]);

    post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    assertAuthenticatedAs($user);
});

it('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/');

    assertGuest();
});
