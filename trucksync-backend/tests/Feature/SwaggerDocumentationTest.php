<?php

use App\OpenApi\OpenApiSpec;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;

it('serves the Swagger UI', function () {
    $this->get('/api/documentation')
        ->assertOk()
        ->assertSee('SwaggerUIBundle', false);
});

it('serves the OpenAPI document', function () {
    $response = $this->getJson('/api/documentation/openapi.json');

    $response->assertOk();

    $spec = $response->json();

    expect($spec['openapi'])->toBe('3.0.3')
        ->and($spec['paths']['/api/auth/register']['post']['operationId'])->toBe('registerUser')
        ->and($spec['paths']['/api/auth/login']['post']['operationId'])->toBe('loginUser')
        ->and($spec['paths']['/api/auth/me']['get']['security'][0])->toHaveKey('sanctumBearer')
        ->and($spec['paths']['/api/auth/logout']['post']['security'][0])->toHaveKey('sanctumBearer');
});

it('documents every controller-backed API route', function () {
    $spec = OpenApiSpec::make('http://localhost');

    $documentedRoutes = collect($spec['paths'])
        ->flatMap(fn (array $operations, string $path): array => collect($operations)
            ->keys()
            ->map(fn (string $method): string => strtoupper($method).' '.$path)
            ->all())
        ->values()
        ->all();

    $controllerRoutes = collect(Route::getRoutes())
        ->flatMap(function (LaravelRoute $route): array {
            if (! is_string($route->getAction('controller')) || ! str_starts_with($route->uri(), 'api/')) {
                return [];
            }

            return collect($route->methods())
                ->reject(fn (string $method): bool => in_array($method, ['HEAD', 'OPTIONS'], true))
                ->map(fn (string $method): string => $method.' /'.$route->uri())
                ->all();
        })
        ->values()
        ->all();

    expect($controllerRoutes)->not->toBeEmpty()
        ->and(array_values(array_diff($controllerRoutes, $documentedRoutes)))->toBe([]);
});
