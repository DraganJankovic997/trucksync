<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class CountrySeeder extends Seeder
{
    /**
     * Import countries from the bundled countries JSON file.
     */
    public function run(): void
    {
        $exitCode = Artisan::call('countries:import');

        if ($exitCode !== 0) {
            throw new RuntimeException(trim(Artisan::output()) ?: 'Unable to import countries.');
        }
    }
}
