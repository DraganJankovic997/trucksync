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
            ],
            'paths' => [
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
                    'User' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'first_name',
                            'last_name',
                            'email',
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
