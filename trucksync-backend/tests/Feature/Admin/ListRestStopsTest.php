<?php

use App\Models\RestStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('lists rest stops for admin filters', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin'));
    $firstRestStop = createRestStopForAdminReports('Roadside', 'Alpha', true);
    $secondRestStop = createRestStopForAdminReports('Transit', 'Beta', false);

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/rest-stops')
        ->assertOk()
        ->assertJsonCount(2, 'data.rest_stops')
        ->assertJsonPath('data.rest_stops.0.id', $firstRestStop->id)
        ->assertJsonPath('data.rest_stops.0.company_name', 'Roadside Alpha')
        ->assertJsonPath('data.rest_stops.0.is_approved', true)
        ->assertJsonPath('data.rest_stops.1.id', $secondRestStop->id)
        ->assertJsonPath('data.rest_stops.1.company_name', 'Transit Beta')
        ->assertJsonPath('data.rest_stops.1.is_approved', false);
});

it('forbids non-admin users from listing rest stops', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/admin/rest-stops')
        ->assertForbidden()
        ->assertJsonPath('message', 'User does not have the right roles.');
});

it('requires authentication to list rest stops', function () {
    $this->getJson('/api/admin/rest-stops')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

function createRestStopForAdminReports(
    string $firstName,
    string $lastName,
    bool $isApproved
): RestStop {
    return RestStop::query()->create([
        'user_id' => User::factory()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'profile_type' => 'rest_stop',
        ])->id,
        'city' => fake()->city(),
        'address' => fake()->streetAddress(),
        'post_code' => fake()->postcode(),
        'works_from' => '08:00',
        'works_to' => '22:00',
        'is_approved' => $isApproved,
    ]);
}
