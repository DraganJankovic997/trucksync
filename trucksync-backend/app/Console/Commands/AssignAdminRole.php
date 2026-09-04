<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:assign-admin {email : Email address of the user account.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign the admin role to a user account.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $emailArgument = $this->argument('email');

        if (! is_string($emailArgument)) {
            $this->error('Email must be a string.');

            return self::FAILURE;
        }

        $email = strtolower(trim($emailArgument));

        if ($email === '') {
            $this->error('Email is required.');

            return self::FAILURE;
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user) {
            $this->error("User not found for email: {$email}");

            return self::FAILURE;
        }

        $role = Role::findOrCreate('admin');
        $alreadyAssigned = $user->hasRole($role);

        $user->assignRole($role);

        if ($alreadyAssigned) {
            $this->info("{$user->email} already has the admin role.");
        } else {
            $this->info("Assigned admin role to {$user->email}.");
        }

        return self::SUCCESS;
    }
}
