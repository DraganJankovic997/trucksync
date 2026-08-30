<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('registers a new user', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'Sam',
        'last_name' => 'Driver',
        'email' => 'sam.driver@example.com',
        'password' => 'secure-password',
        'password_confirmation' => 'secure-password',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Account created successfully.')
        ->assertJsonPath('data.user.first_name', 'Sam')
        ->assertJsonPath('data.user.last_name', 'Driver')
        ->assertJsonPath('data.user.email', 'sam.driver@example.com')
        ->assertJsonMissingPath('data.user.name')
        ->assertJsonMissingPath('data.user.password');

    $user = User::query()->where('email', 'sam.driver@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->first_name)->toBe('Sam')
        ->and($user->last_name)->toBe('Driver')
        ->and(Hash::check('secure-password', $user->password))->toBeTrue();
});

it('validates required registration fields', function () {
    $response = $this->postJson('/api/auth/register', []);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'email',
            'password',
        ]);
});

it('validates unique email addresses and password confirmation', function () {
    User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'Jamie',
        'last_name' => 'Dispatcher',
        'email' => 'taken@example.com',
        'password' => 'secure-password',
        'password_confirmation' => 'different-password',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'email',
            'password',
        ]);
});
