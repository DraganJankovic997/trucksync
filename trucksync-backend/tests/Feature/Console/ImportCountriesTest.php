<?php

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('imports countries from JSON and replaces existing countries', function () {
    Country::query()->create([
        'code' => 'ZZ',
        'name' => 'Old Country',
    ]);

    $path = tempnam(sys_get_temp_dir(), 'countries');

    file_put_contents($path, json_encode([
        'countries' => [
            [
                'code' => 'rs',
                'name' => 'Serbia',
                'capital' => 'Belgrade',
            ],
            [
                'code' => 'DE',
                'name' => 'Germany',
            ],
        ],
    ], JSON_THROW_ON_ERROR));

    try {
        $this->artisan('countries:import', ['--path' => $path])
            ->expectsOutput('Imported 2 countries.')
            ->assertExitCode(0);
    } finally {
        unlink($path);
    }

    $this->assertDatabaseCount('countries', 2);
    $this->assertDatabaseHas('countries', [
        'code' => 'RS',
        'name' => 'Serbia',
    ]);
    $this->assertDatabaseHas('countries', [
        'code' => 'DE',
        'name' => 'Germany',
    ]);
    $this->assertDatabaseMissing('countries', [
        'code' => 'ZZ',
        'name' => 'Old Country',
    ]);
});
