<?php

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('creates the admin role', function () {
    $this->artisan('roles:create')
        ->expectsOutput('Created admin role.')
        ->assertExitCode(Command::SUCCESS);

    $this->assertDatabaseHas('roles', [
        'name' => 'admin',
        'guard_name' => 'web',
    ]);
});

it('does not duplicate the admin role', function () {
    Role::findOrCreate('admin');

    $this->artisan('roles:create')
        ->expectsOutput('Admin role already exists.')
        ->assertExitCode(Command::SUCCESS);

    expect(Role::query()->where('name', 'admin')->count())->toBe(1);
});
