<?php

use App\Models\User;
use App\Models\Stage;
use function Pest\Laravel\{actingAs, delete, get, post, put};

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->stage = Stage::factory()->create();
});

it('admin can create a stage', function () {
    actingAs($this->admin)
        ->post(route('admin.stages.store'), [
            'title' => 'New Stage',
            'title_ar' => 'مرحلة جديدة',
            'order' => 2,
            'time_limit_minutes' => 30,
            'passing_percentage' => 60,
            'points_reward' => 20,
        ])
        ->assertRedirect(route('admin.stages.index'));

    $this->assertDatabaseHas('stages', ['title' => 'New Stage']);
});

it('admin can update a stage', function () {
    actingAs($this->admin)
        ->put(route('admin.stages.update', $this->stage), [
            'title' => 'Updated Stage',
            'order' => 1,
            'time_limit_minutes' => 45,
            'passing_percentage' => 75,
            'points_reward' => 50,
        ])
        ->assertRedirect(route('admin.stages.index'));

    $this->assertDatabaseHas('stages', ['title' => 'Updated Stage']);
});

it('admin can delete a stage', function () {
    actingAs($this->admin)
        ->delete(route('admin.stages.destroy', $this->stage))
        ->assertRedirect(route('admin.stages.index'));

    $this->assertSoftDeleted('stages', ['id' => $this->stage->id]);
});
