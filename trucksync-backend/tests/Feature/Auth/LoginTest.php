<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

it('logs in a user and returns a bearer token', function () {
    $user = User::factory()->create([
        'first_name' => 'Sam',
        'last_name' => 'Driver',
        'email' => 'sam.driver@example.com',
        'password' => Hash::make('secure-password'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => '  SAM.DRIVER@EXAMPLE.COM  ',
        'password' => 'secure-password',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Logged in successfully.')
        ->assertJsonMissingPath('data.user')
        ->assertJsonMissingPath('data.user.password');

    $token = $response->json('data.token');
    $accessToken = PersonalAccessToken::findToken($token);

    expect($token)->toBeString()->not->toBeEmpty()
        ->and($accessToken)->not->toBeNull()
        ->and($accessToken->tokenable->is($user))->toBeTrue();
});

it('rejects invalid login credentials', function () {
    User::factory()->create([
        'email' => 'sam.driver@example.com',
        'password' => Hash::make('secure-password'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'sam.driver@example.com',
        'password' => 'wrong-password',
    ]);

    $response
        ->assertUnauthorized()
        ->assertJsonPath('message', 'The provided credentials are invalid.');
});

it('returns not found when login user does not exist', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'missing.driver@example.com',
        'password' => 'secure-password',
    ]);

    $response
        ->assertNotFound()
        ->assertJsonPath('message', 'User not found.');
});

it('validates required login fields', function () {
    $response = $this->postJson('/api/auth/login', []);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'email',
            'password',
        ]);
});

it('returns the authenticated user for a valid token', function () {
    $user = User::factory()->create([
        'email' => 'sam.driver@example.com',
        'password' => Hash::make('secure-password'),
    ]);

    $token = $this->postJson('/api/auth/login', [
        'email' => 'sam.driver@example.com',
        'password' => 'secure-password',
    ])->json('data.token');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/auth/me');

    $response
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', 'sam.driver@example.com')
        ->assertJsonMissingPath('data.user.password');
});

it('requires a valid token for me', function () {
    $this->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');

    $this->withHeader('Authorization', 'Bearer invalid-token')
        ->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

it('logs out and invalidates the current token', function () {
    User::factory()->create([
        'email' => 'sam.driver@example.com',
        'password' => Hash::make('secure-password'),
    ]);

    $token = $this->postJson('/api/auth/login', [
        'email' => 'sam.driver@example.com',
        'password' => 'secure-password',
    ])->json('data.token');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/auth/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logged out successfully.');

    expect(PersonalAccessToken::findToken($token))->toBeNull();

    Auth::forgetGuards();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/auth/me')
        ->assertUnauthorized();
});
