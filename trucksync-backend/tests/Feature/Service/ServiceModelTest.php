<?php

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('stores catalogue services with only an id and name', function () {
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    expect(Schema::getColumnListing('services'))->toBe(['id', 'name']);

    $this->assertDatabaseHas('services', [
        'id' => $service->id,
        'name' => 'Tire replacement',
    ]);
});
