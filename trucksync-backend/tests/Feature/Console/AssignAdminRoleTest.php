<?php

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('assigns the admin role to a user by email', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    $this->artisan('roles:assign-admin', ['email' => '  ADMIN@example.com  '])
        ->expectsOutput('Assigned admin role to admin@example.com.')
        ->assertExitCode(Command::SUCCESS);

    expect($user->refresh()->hasRole('admin'))->toBeTrue();

    $this->assertDatabaseHas('model_has_roles', [
        'model_id' => $user->id,
        'model_type' => User::class,
    ]);
});

it('does not duplicate an existing admin role assignment', function () {
    $role = Role::findOrCreate('admin');
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $user->assignRole($role);

    $this->artisan('roles:assign-admin', ['email' => 'admin@example.com'])
        ->expectsOutput('admin@example.com already has the admin role.')
        ->assertExitCode(Command::SUCCESS);

    expect($user->roles()->where('roles.name', 'admin')->count())->toBe(1);
});

it('fails when the user does not exist', function () {
    $this->artisan('roles:assign-admin', ['email' => 'missing@example.com'])
        ->expectsOutput('User not found for email: missing@example.com')
        ->assertExitCode(Command::FAILURE);
});

it('fails when the email is empty', function () {
    $this->artisan('roles:assign-admin', ['email' => '   '])
        ->expectsOutput('Email is required.')
        ->assertExitCode(Command::FAILURE);
});
