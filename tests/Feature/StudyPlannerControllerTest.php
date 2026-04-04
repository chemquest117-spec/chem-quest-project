<?php

use App\Models\Stage;
use App\Models\StudyPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase $this */
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Ensure we have stages to test with
    Stage::factory()->count(3)->create();
});

test('study planner index shows active plan', function () {
    $plan = StudyPlan::factory()->create([
        'user_id' => $this->user->id,
        'status' => StudyPlan::STATUS_ACTIVE,
    ]);

    $response = $this->get(route('planner.index'));

    $response->assertStatus(200)
        ->assertViewHas('activePlan')
        ->assertSee(__('planner.title'));
});

test('study planner index shows setup when no plan', function () {
    $response = $this->get(route('planner.index'));

    $response->assertStatus(200)
        ->assertViewHas('activePlan', null)
        ->assertSee(__('planner.get_started'));
});

test('store generates a new plan', function () {
    $response = $this->post(route('planner.store'), [
        'start_date' => now()->toDateString(),
        'exam_date' => now()->addDays(14)->toDateString(),
        'preferred_days' => ['mon', 'tue', 'wed'],
        'hours_per_day' => 2,
        'pace' => 'medium',
    ]);

    $plan = StudyPlan::where('user_id', $this->user->id)->first();

    expect($plan)->not->toBeNull();
    $response->assertRedirect(route('planner.show', $plan));
});

test('toggle item completion', function () {
    $plan = StudyPlan::factory()->create(['user_id' => $this->user->id]);
    $item = $plan->items()->create([
        'stage_id' => Stage::first()->id,
        'scheduled_date' => now(),
        'is_completed' => false,
        'estimated_minutes' => 60,
    ]);

    $response = $this->post(route('planner.items.toggle', $item));

    $response->assertSessionHas('success');
    expect($item->fresh()->is_completed)->toBeTrue();
});

test('user cannot access others study plan', function () {
    $otherUser = User::factory()->create();
    $plan = StudyPlan::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->get(route('planner.show', $plan));

    $response->assertStatus(403);
});
