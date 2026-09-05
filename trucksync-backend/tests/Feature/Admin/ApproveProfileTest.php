<?php

use App\Models\Dispatcher;
use App\Models\RestStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('approves a dispatcher profile for an admin user', function () {
    $targetUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = Dispatcher::query()->create([
        'user_id' => $targetUser->id,
        'company_name' => 'Acme Dispatch',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => 'REG-1234',
        'is_approved' => false,
    ]);
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($admin);

    $this->postJson("/api/admin/approve/{$targetUser->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Profile approved successfully.')
        ->assertJsonPath('data.approval.profile_id', $dispatcher->id)
        ->assertJsonPath('data.approval.user_id', $targetUser->id)
        ->assertJsonPath('data.approval.profile_type', 'dispatcher')
        ->assertJsonPath('data.approval.is_approved', true);

    expect($dispatcher->refresh()->is_approved)->toBeTrue();
});

it('approves a rest stop profile for an admin user', function () {
    $targetUser = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = RestStop::query()->create([
        'user_id' => $targetUser->id,
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
        'is_approved' => false,
    ]);
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($admin);

    $this->postJson("/api/admin/approve/{$targetUser->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Profile approved successfully.')
        ->assertJsonPath('data.approval.profile_id', $restStop->id)
        ->assertJsonPath('data.approval.user_id', $targetUser->id)
        ->assertJsonPath('data.approval.profile_type', 'rest_stop')
        ->assertJsonPath('data.approval.is_approved', true);

    expect($restStop->refresh()->is_approved)->toBeTrue();
});

it('forbids non-admin users from approving a profile', function () {
    $targetUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $dispatcher = Dispatcher::query()->create([
        'user_id' => $targetUser->id,
        'company_name' => 'Acme Dispatch',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => 'REG-1234',
        'is_approved' => false,
    ]);

    Sanctum::actingAs(User::factory()->create());

    $this->postJson("/api/admin/approve/{$targetUser->id}")
        ->assertForbidden()
        ->assertJsonPath('message', 'User does not have the right roles.');

    expect($dispatcher->refresh()->is_approved)->toBeFalse();
});

it('requires authentication to approve a profile', function () {
    $this->postJson('/api/admin/approve/999')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('returns not found when the user has no approvable profile', function () {
    $targetUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($admin);

    $this->postJson("/api/admin/approve/{$targetUser->id}")
        ->assertNotFound()
        ->assertJsonPath('message', 'Approvable profile not found.');
});
