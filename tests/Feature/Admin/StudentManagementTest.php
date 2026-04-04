<?php

use App\Models\User;
use function Pest\Laravel\{actingAs, delete, get, post};

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->student = User::factory()->create(['is_admin' => false]);
});

it('admin can view student list', function () {
    actingAs($this->admin)
        ->get(route('admin.students.index'))
        ->assertOk()
        ->assertSee($this->student->name);
});

it('admin can ban a student', function () {
    actingAs($this->admin)
        ->from(route('admin.students.index'))
        ->post(route('admin.students.toggleBan', $this->student))
        ->assertRedirect(route('admin.students.index'))
        ->assertSessionHas('success');

    expect($this->student->refresh()->is_banned)->toBeTrue();
});

it('admin can delete a student', function () {
    actingAs($this->admin)
        ->delete(route('admin.students.destroy', $this->student->id))
        ->assertRedirect(route('admin.students.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('users', ['id' => $this->student->id]);
});

it('student cannot access admin dashboard', function () {
    actingAs($this->student)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});
