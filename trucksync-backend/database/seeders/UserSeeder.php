<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Seed application users.
     */
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Driver',
                'last_name' => 'Seed',
                'country' => 'Serbia',
                'phone_number' => '+381601111111',
                'email' => 'driver@trucksync.com',
                'profile_type' => 'driver',
            ],
            [
                'first_name' => 'Dispatcher',
                'last_name' => 'Seed',
                'country' => 'Serbia',
                'phone_number' => '+381602222222',
                'email' => 'dispatcher@trucksync.com',
                'profile_type' => 'dispatcher',
            ],
            [
                'first_name' => 'RestStop',
                'last_name' => 'Seed',
                'country' => 'Serbia',
                'phone_number' => '+381603333333',
                'email' => 'reststop@trucksync.com',
                'profile_type' => 'rest_stop',
            ],
            [
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'country' => 'Serbia',
                'phone_number' => '+381604444444',
                'email' => 'admin@trucksync.com',
                'profile_type' => null,
            ],
        ];

        foreach ($users as $user) {
            $seededUser = User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    ...$user,
                    'password' => Hash::make('password'),
                ],
            );

            $seededUser->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        $admin = User::query()
            ->where('email', 'admin@trucksync.com')
            ->firstOrFail();

        $admin->assignRole(Role::findOrCreate('admin'));
    }
}
