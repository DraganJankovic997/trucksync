<?php

use App\Models\Dispatcher;
use App\Models\RestStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('lists dispatcher and rest stop profiles needing approval for an admin user', function () {
    $pendingDispatcherUser = User::factory()->create([
        'first_name' => 'Dana',
        'last_name' => 'Dispatcher',
        'email' => 'dana.dispatcher@example.com',
        'country' => 'Serbia',
        'phone_number' => '+381601111111',
        'profile_type' => 'dispatcher',
    ]);
    $pendingDispatcher = Dispatcher::query()->create([
        'user_id' => $pendingDispatcherUser->id,
        'company_name' => 'Acme Dispatch',
        'city' => 'Belgrade',
        'address' => 'Main Street 1',
        'post_code' => '11000',
        'registration_number' => 'REG-1234',
        'is_approved' => false,
    ]);
    $approvedDispatcherUser = User::factory()->create([
        'profile_type' => 'dispatcher',
    ]);
    Dispatcher::query()->create([
        'user_id' => $approvedDispatcherUser->id,
        'company_name' => 'Approved Dispatch',
        'city' => 'Novi Sad',
        'address' => 'River Street 2',
        'post_code' => '21000',
        'registration_number' => 'REG-5678',
        'is_approved' => true,
    ]);
    $pendingRestStopUser = User::factory()->create([
        'first_name' => 'Riley',
        'last_name' => 'Reststop',
        'email' => 'riley.reststop@example.com',
        'country' => 'Serbia',
        'phone_number' => '+381602222222',
        'profile_type' => 'rest_stop',
    ]);
    $pendingRestStop = RestStop::query()->create([
        'user_id' => $pendingRestStopUser->id,
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
        'is_approved' => false,
    ]);
    $approvedRestStopUser = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    RestStop::query()->create([
        'user_id' => $approvedRestStopUser->id,
        'city' => 'Subotica',
        'address' => 'North Road 7',
        'post_code' => '24000',
        'works_from' => '07:00',
        'works_to' => '20:00',
        'is_approved' => true,
    ]);
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/approve')
        ->assertOk()
        ->assertJsonCount(1, 'data.dispatchers')
        ->assertJsonCount(1, 'data.rest_stops')
        ->assertJsonPath('data.dispatchers.0.id', $pendingDispatcher->id)
        ->assertJsonPath('data.dispatchers.0.user_id', $pendingDispatcherUser->id)
        ->assertJsonPath('data.dispatchers.0.company_name', 'Acme Dispatch')
        ->assertJsonPath('data.dispatchers.0.is_approved', false)
        ->assertJsonPath('data.dispatchers.0.user.id', $pendingDispatcherUser->id)
        ->assertJsonPath('data.dispatchers.0.user.first_name', 'Dana')
        ->assertJsonPath('data.dispatchers.0.user.last_name', 'Dispatcher')
        ->assertJsonPath('data.dispatchers.0.user.email', 'dana.dispatcher@example.com')
        ->assertJsonPath('data.dispatchers.0.user.country', 'Serbia')
        ->assertJsonPath('data.dispatchers.0.user.phone_number', '+381601111111')
        ->assertJsonPath('data.dispatchers.0.user.profile_type', 'dispatcher')
        ->assertJsonPath('data.rest_stops.0.id', $pendingRestStop->id)
        ->assertJsonPath('data.rest_stops.0.user_id', $pendingRestStopUser->id)
        ->assertJsonPath('data.rest_stops.0.city', 'Belgrade')
        ->assertJsonPath('data.rest_stops.0.works_from', '08:00')
        ->assertJsonPath('data.rest_stops.0.works_to', '22:00')
        ->assertJsonPath('data.rest_stops.0.is_approved', false)
        ->assertJsonPath('data.rest_stops.0.user.id', $pendingRestStopUser->id)
        ->assertJsonPath('data.rest_stops.0.user.first_name', 'Riley')
        ->assertJsonPath('data.rest_stops.0.user.last_name', 'Reststop')
        ->assertJsonPath('data.rest_stops.0.user.email', 'riley.reststop@example.com')
        ->assertJsonPath('data.rest_stops.0.user.country', 'Serbia')
        ->assertJsonPath('data.rest_stops.0.user.phone_number', '+381602222222')
        ->assertJsonPath('data.rest_stops.0.user.profile_type', 'rest_stop');
});

it('returns empty pending approval lists when no profiles need approval', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin'));

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/approve')
        ->assertOk()
        ->assertJsonPath('data.dispatchers', [])
        ->assertJsonPath('data.rest_stops', []);
});

it('forbids non-admin users from listing profiles needing approval', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/admin/approve')
        ->assertForbidden()
        ->assertJsonPath('message', 'User does not have the right roles.');
});

it('requires authentication to list profiles needing approval', function () {
    $this->getJson('/api/admin/approve')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('approves a dispatcher profile for an admin user', function () {
    $targetUser = User::factory()->create([
        'first_name' => 'Dana',
        'last_name' => 'Dispatcher',
        'email' => 'dana.approve@example.com',
        'country' => 'Serbia',
        'phone_number' => '+381601111111',
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
        ->assertJsonPath('data.approval.is_approved', true)
        ->assertJsonPath('data.approval.user.id', $targetUser->id)
        ->assertJsonPath('data.approval.user.first_name', 'Dana')
        ->assertJsonPath('data.approval.user.last_name', 'Dispatcher')
        ->assertJsonPath('data.approval.user.email', 'dana.approve@example.com')
        ->assertJsonPath('data.approval.user.country', 'Serbia')
        ->assertJsonPath('data.approval.user.phone_number', '+381601111111')
        ->assertJsonPath('data.approval.user.profile_type', 'dispatcher');

    expect($dispatcher->refresh()->is_approved)->toBeTrue();
});

it('approves a rest stop profile for an admin user', function () {
    $targetUser = User::factory()->create([
        'first_name' => 'Riley',
        'last_name' => 'Reststop',
        'email' => 'riley.approve@example.com',
        'country' => 'Serbia',
        'phone_number' => '+381602222222',
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
        ->assertJsonPath('data.approval.is_approved', true)
        ->assertJsonPath('data.approval.user.id', $targetUser->id)
        ->assertJsonPath('data.approval.user.first_name', 'Riley')
        ->assertJsonPath('data.approval.user.last_name', 'Reststop')
        ->assertJsonPath('data.approval.user.email', 'riley.approve@example.com')
        ->assertJsonPath('data.approval.user.country', 'Serbia')
        ->assertJsonPath('data.approval.user.phone_number', '+381602222222')
        ->assertJsonPath('data.approval.user.profile_type', 'rest_stop');

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
