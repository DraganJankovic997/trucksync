<?php

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('stores catalogue services with an optional measurement unit', function () {
    $service = Service::query()->create([
        'name' => 'Tire replacement',
        'measurement_unit' => 'tire',
    ]);
    $serviceWithoutMeasurementUnit = Service::query()->create([
        'name' => 'Parking',
    ]);

    expect(Schema::getColumnListing('services'))->toBe([
        'id',
        'name',
        'measurement_unit',
    ]);

    $this->assertDatabaseHas('services', [
        'id' => $service->id,
        'name' => 'Tire replacement',
        'measurement_unit' => 'tire',
    ]);

    $this->assertDatabaseHas('services', [
        'id' => $serviceWithoutMeasurementUnit->id,
        'name' => 'Parking',
        'measurement_unit' => null,
    ]);
});
