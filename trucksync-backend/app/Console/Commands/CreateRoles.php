<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create application roles.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $role = Role::findOrCreate('admin');

        if ($role->wasRecentlyCreated) {
            $this->info('Created admin role.');
        } else {
            $this->info('Admin role already exists.');
        }

        return self::SUCCESS;
    }
}
