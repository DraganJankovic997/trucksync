<?php

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists countries without authentication', function () {
    Country::query()->create([
        'code' => 'RS',
        'name' => 'Serbia',
    ]);
    Country::query()->create([
        'code' => 'DE',
        'name' => 'Germany',
    ]);

    $this->getJson('/api/countries')
        ->assertOk()
        ->assertJsonPath('data.countries.0.code', 'DE')
        ->assertJsonPath('data.countries.0.name', 'Germany')
        ->assertJsonPath('data.countries.1.code', 'RS')
        ->assertJsonPath('data.countries.1.name', 'Serbia');
});
