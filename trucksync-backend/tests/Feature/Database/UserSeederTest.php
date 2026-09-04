<?php

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('seeds the application users', function () {
    $users = [
        'driver@trucksync.com' => [
            'first_name' => 'Driver',
            'last_name' => 'Seed',
            'profile_type' => 'driver',
        ],
        'dispatcher@trucksync.com' => [
            'first_name' => 'Dispatcher',
            'last_name' => 'Seed',
            'profile_type' => 'dispatcher',
        ],
        'reststop@trucksync.com' => [
            'first_name' => 'RestStop',
            'last_name' => 'Seed',
            'profile_type' => 'rest_stop',
        ],
        'admin@trucksync.com' => [
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'profile_type' => null,
        ],
    ];

    $this->seed(UserSeeder::class);
    $this->seed(UserSeeder::class);

    foreach ($users as $email => $expectedUser) {
        $user = User::query()
            ->where('email', $email)
            ->firstOrFail();

        expect($user->first_name)->toBe($expectedUser['first_name'])
            ->and($user->last_name)->toBe($expectedUser['last_name'])
            ->and($user->profile_type)->toBe($expectedUser['profile_type']);

        expect($user->email_verified_at)->not->toBeNull();
        expect(Hash::check('password', $user->password))->toBeTrue();
    }

    $admin = User::query()
        ->where('email', 'admin@trucksync.com')
        ->firstOrFail();

    expect(User::query()->whereIn('email', array_keys($users))->count())->toBe(4)
        ->and(Role::query()->where('name', 'admin')->count())->toBe(1)
        ->and($admin->hasRole('admin'))->toBeTrue();
});
