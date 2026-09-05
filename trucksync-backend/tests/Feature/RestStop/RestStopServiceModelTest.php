<?php

use App\Models\RestStop;
use App\Models\RestStopService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('stores rest stop services with only rest stop and service ids', function () {
    $user = User::factory()->create([
        'profile_type' => 'rest_stop',
    ]);
    $restStop = RestStop::query()->create([
        'user_id' => $user->id,
        'country' => 'Serbia',
        'city' => 'Belgrade',
        'address' => 'Highway 1',
        'post_code' => '11000',
        'works_from' => '08:00',
        'works_to' => '22:00',
    ]);
    $service = Service::query()->create([
        'name' => 'Tire replacement',
    ]);

    $restStopService = RestStopService::query()->create([
        'rest_stop_id' => $restStop->id,
        'service_id' => $service->id,
    ]);

    expect(Schema::getColumnListing('rest_stop_services'))->toBe([
        'rest_stop_id',
        'service_id',
    ]);

    $this->assertDatabaseHas('rest_stop_services', [
        'rest_stop_id' => $restStop->id,
        'service_id' => $service->id,
    ]);

    expect($restStopService->restStop->is($restStop))->toBeTrue()
        ->and($restStopService->service->is($service))->toBeTrue();
});
