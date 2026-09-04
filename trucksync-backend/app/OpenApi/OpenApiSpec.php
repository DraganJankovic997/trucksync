<?php

namespace App\OpenApi;

class OpenApiSpec
{
    /**
     * @return array<string, mixed>
     */
    public static function make(?string $serverUrl = null): array
    {
        $serverUrl = rtrim($serverUrl ?: (string) config('app.url'), '/');

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => config('app.name', 'TruckSync').' API',
                'description' => 'OpenAPI documentation for the TruckSync backend.',
                'version' => '1.0.0',
            ],
            'servers' => [
                [
                    'url' => $serverUrl,
                    'description' => 'Current backend server',
                ],
            ],
            'tags' => [
                [
                    'name' => 'Authentication',
                    'description' => 'Registration, login, profile, and logout endpoints.',
                ],
                [
                    'name' => 'Users',
                    'description' => 'Authenticated user profile management.',
                ],
                [
                    'name' => 'Drivers',
                    'description' => 'Authenticated driver profile management.',
                ],
                [
                    'name' => 'Countries',
                    'description' => 'Country reference data.',
                ],
            ],
            'paths' => [
                '/api/countries' => [
                    'get' => [
                        'tags' => ['Countries'],
                        'summary' => 'List countries',
                        'operationId' => 'listCountries',
                        'responses' => [
                            '200' => [
                                'description' => 'Country list.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/CountriesIndexResponse',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/api/auth/register' => [
                    'post' => [
                        'tags' => ['Authentication'],
                        'summary' => 'Register a user',
                        'operationId' => 'registerUser',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/RegisterRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Account created successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/RegisterResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '422' => [
                                '$ref' => '#/components/responses/ValidationError',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/auth/login' => [
                    'post' => [
                        'tags' => ['Authentication'],
                        'summary' => 'Log in a user',
                        'operationId' => 'loginUser',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/LoginRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Logged in successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/LoginResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/InvalidCredentials',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/UserNotFound',
                            ],
                            '422' => [
                                '$ref' => '#/components/responses/ValidationError',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/auth/me' => [
                    'get' => [
                        'tags' => ['Authentication'],
                        'summary' => 'Get the authenticated user',
                        'operationId' => 'getAuthenticatedUser',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Authenticated user profile.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/CurrentUserResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                        ],
                    ],
                ],
                '/api/auth/logout' => [
                    'post' => [
                        'tags' => ['Authentication'],
                        'summary' => 'Log out the authenticated user',
                        'operationId' => 'logoutUser',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Logged out successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/MessageResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/user' => [
                    'put' => [
                        'tags' => ['Users'],
                        'summary' => 'Update the authenticated user',
                        'operationId' => 'updateAuthenticatedUser',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/UserUpdateRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'User updated successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/UserUpdateResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '422' => [
                                '$ref' => '#/components/responses/ValidationError',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/driver' => [
                    'post' => [
                        'tags' => ['Drivers'],
                        'summary' => 'Create or update the authenticated driver profile',
                        'operationId' => 'upsertAuthenticatedDriver',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/DriverUpsertRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Driver profile updated successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/DriverUpsertResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '201' => [
                                'description' => 'Driver profile created successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/DriverUpsertResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/Forbidden',
                            ],
                            '422' => [
                                '$ref' => '#/components/responses/ValidationError',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'sanctumBearer' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Sanctum token',
                        'description' => 'Laravel Sanctum bearer token returned by the login endpoint.',
                    ],
                ],
                'schemas' => [
                    'RegisterRequest' => [
                        'type' => 'object',
                        'required' => [
                            'first_name',
                            'last_name',
                            'email',
                            'password',
                            'password_confirmation',
                            'profile_type',
                        ],
                        'properties' => [
                            'first_name' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'Sam',
                            ],
                            'last_name' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'Driver',
                            ],
                            'email' => [
                                'type' => 'string',
                                'format' => 'email',
                                'description' => 'Must be unique.',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'sam.driver@example.com',
                            ],
                            'password' => [
                                'type' => 'string',
                                'format' => 'password',
                                'minLength' => 8,
                                'example' => 'secure-password',
                            ],
                            'password_confirmation' => [
                                'type' => 'string',
                                'format' => 'password',
                                'description' => 'Must match password.',
                                'minLength' => 8,
                                'example' => 'secure-password',
                            ],
                            'profile_type' => [
                                'type' => 'string',
                                'enum' => ['driver', 'dispatcher', 'rest_stop'],
                                'example' => 'driver',
                            ],
                        ],
                    ],
                    'LoginRequest' => [
                        'type' => 'object',
                        'required' => [
                            'email',
                            'password',
                        ],
                        'properties' => [
                            'email' => [
                                'type' => 'string',
                                'format' => 'email',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'sam.driver@example.com',
                            ],
                            'password' => [
                                'type' => 'string',
                                'format' => 'password',
                                'minLength' => 1,
                                'example' => 'secure-password',
                            ],
                        ],
                    ],
                    'Country' => [
                        'type' => 'object',
                        'required' => [
                            'code',
                            'name',
                        ],
                        'properties' => [
                            'code' => [
                                'type' => 'string',
                                'minLength' => 2,
                                'maxLength' => 2,
                                'example' => 'RS',
                            ],
                            'name' => [
                                'type' => 'string',
                                'example' => 'Serbia',
                            ],
                        ],
                    ],
                    'CountriesIndexResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['countries'],
                                'properties' => [
                                    'countries' => [
                                        'type' => 'array',
                                        'items' => [
                                            '$ref' => '#/components/schemas/Country',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'User' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'first_name',
                            'last_name',
                            'email',
                            'country',
                            'phone_number',
                            'profile_type',
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'first_name' => [
                                'type' => 'string',
                                'nullable' => true,
                                'example' => 'Sam',
                            ],
                            'last_name' => [
                                'type' => 'string',
                                'nullable' => true,
                                'example' => 'Driver',
                            ],
                            'email' => [
                                'type' => 'string',
                                'format' => 'email',
                                'example' => 'sam.driver@example.com',
                            ],
                            'country' => [
                                'type' => 'string',
                                'nullable' => true,
                                'example' => 'Serbia',
                            ],
                            'phone_number' => [
                                'type' => 'string',
                                'nullable' => true,
                                'maxLength' => 30,
                                'example' => '+381601234567',
                            ],
                            'profile_type' => [
                                'type' => 'string',
                                'nullable' => true,
                                'enum' => ['driver', 'dispatcher', 'rest_stop'],
                                'example' => 'driver',
                            ],
                        ],
                    ],
                    'Driver' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'user_id',
                            'dispatcher_id',
                            'license_number',
                            'is_dispatcher_approved',
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'user_id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'dispatcher_id' => [
                                'type' => 'integer',
                                'nullable' => true,
                                'example' => 2,
                            ],
                            'license_number' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'D1234567',
                            ],
                            'is_dispatcher_approved' => [
                                'type' => 'boolean',
                                'example' => false,
                            ],
                        ],
                    ],
                    'UserUpdateRequest' => [
                        'type' => 'object',
                        'required' => [
                            'first_name',
                            'last_name',
                            'email',
                            'country',
                            'phone_number',
                        ],
                        'properties' => [
                            'first_name' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'Sam',
                            ],
                            'last_name' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'Driver',
                            ],
                            'email' => [
                                'type' => 'string',
                                'format' => 'email',
                                'description' => 'Must be unique, except for the authenticated user.',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'sam.driver@example.com',
                            ],
                            'country' => [
                                'type' => 'string',
                                'description' => 'Country name matching countries.name.',
                                'maxLength' => 255,
                                'example' => 'Serbia',
                            ],
                            'phone_number' => [
                                'type' => 'string',
                                'maxLength' => 30,
                                'example' => '+381601234567',
                            ],
                        ],
                    ],
                    'DriverUpsertRequest' => [
                        'type' => 'object',
                        'required' => [
                            'license_number',
                        ],
                        'properties' => [
                            'license_number' => [
                                'type' => 'string',
                                'description' => 'Must be unique, except for the authenticated driver profile.',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'D1234567',
                            ],
                            'dispatcher_id' => [
                                'type' => 'integer',
                                'nullable' => true,
                                'description' => 'Must reference an existing dispatcher when provided.',
                                'example' => 2,
                            ],
                        ],
                    ],
                    'RegisterResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Account created successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['user'],
                                'properties' => [
                                    'user' => [
                                        '$ref' => '#/components/schemas/User',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'LoginResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Logged in successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['token'],
                                'properties' => [
                                    'token' => [
                                        'type' => 'string',
                                        'example' => '1|sanctum-token-value',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'CurrentUserResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['user'],
                                'properties' => [
                                    'user' => [
                                        '$ref' => '#/components/schemas/User',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'UserUpdateResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'User updated successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['user'],
                                'properties' => [
                                    'user' => [
                                        '$ref' => '#/components/schemas/User',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'DriverUpsertResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Driver profile created successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['driver'],
                                'properties' => [
                                    'driver' => [
                                        '$ref' => '#/components/schemas/Driver',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'MessageResponse' => [
                        'type' => 'object',
                        'required' => ['message'],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Logged out successfully.',
                            ],
                        ],
                    ],
                    'ErrorResponse' => [
                        'type' => 'object',
                        'required' => ['message'],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Unable to process the request.',
                            ],
                        ],
                    ],
                    'ValidationErrorResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'errors',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'The email field is required.',
                            ],
                            'errors' => [
                                'type' => 'object',
                                'additionalProperties' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'string',
                                    ],
                                ],
                                'example' => [
                                    'email' => [
                                        'The email field is required.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    'ValidationError' => [
                        'description' => 'Validation failed.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ValidationErrorResponse',
                                ],
                            ],
                        ],
                    ],
                    'InvalidCredentials' => [
                        'description' => 'The provided credentials are invalid.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'The provided credentials are invalid.',
                                ],
                            ],
                        ],
                    ],
                    'UserNotFound' => [
                        'description' => 'User not found.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'User not found.',
                                ],
                            ],
                        ],
                    ],
                    'Unauthenticated' => [
                        'description' => 'Missing or invalid bearer token.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Unauthenticated.',
                                ],
                            ],
                        ],
                    ],
                    'Forbidden' => [
                        'description' => 'The authenticated user cannot perform this action.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Only driver users can create or update driver profiles.',
                                ],
                            ],
                        ],
                    ],
                    'ServerError' => [
                        'description' => 'Unexpected server error.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
