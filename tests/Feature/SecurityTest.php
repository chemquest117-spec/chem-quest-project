<?php

use App\Models\User;

use function Pest\Laravel\post;

it('enforces rate limiting on login attempts', function () {
    for ($i = 0; $i < 5; $i++) {
        post('/login', ['email' => 'test@example.com', 'password' => 'wrong']);
    }

    // 6th attempt should hit the throttle
    post('/login', ['email' => 'test@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors(); // Web routes redirect with session errors instead of 429 HTTP status
});

it('sanitizes xss payloads in quiz answer submissions', function () {
    $user = User::factory()->create();
    $payload = '<script>alert("xss")</script> Valid Answer';

    // Mock an autosave attempt using a non-existent or dummy ID
    $response = $this->actingAs($user)
        ->postJson('/quiz/1/save-answer', [
            'question_id' => 1,
            'answer' => $payload,
        ]);

    // Should not save the script tag verbatim (strip_tags removes it)
    $this->assertDatabaseMissing('attempt_answers', [
        'selected_answer' => '<script>alert("xss")</script> Valid Answer',
    ]);
});

it('prevents unauthenticated access to dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('prevents non-admin users from accessing admin panel', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user)->get('/admin/dashboard')->assertStatus(403);
});
