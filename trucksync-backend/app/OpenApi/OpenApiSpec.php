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
                    'name' => 'Admin',
                    'description' => 'Administrative profile approval actions.',
                ],
                [
                    'name' => 'Drivers',
                    'description' => 'Authenticated driver profile management.',
                ],
                [
                    'name' => 'Dispatchers',
                    'description' => 'Authenticated dispatcher profile management.',
                ],
                [
                    'name' => 'Routes',
                    'description' => 'Authenticated dispatcher route management.',
                ],
                [
                    'name' => 'Rest Stops',
                    'description' => 'Authenticated rest stop profile management.',
                ],
                [
                    'name' => 'Services',
                    'description' => 'Authenticated service catalogue management.',
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
                '/api/rest-stop/services/{id}' => [
                    'get' => [
                        'tags' => ['Rest Stops'],
                        'summary' => 'List services for a rest stop',
                        'operationId' => 'listRestStopServices',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Rest stop ID.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Rest stop service list.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/RestStopServicesIndexResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/RestStopByIdNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/route/{route_id}' => [
                    'get' => [
                        'tags' => ['Routes'],
                        'summary' => 'Show a route with route stops and needed services',
                        'operationId' => 'showRoute',
                        'parameters' => [
                            [
                                'name' => 'route_id',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Route ID.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Route details.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/RouteShowResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/RouteNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/route-stop/{routeStopId}' => [
                    'get' => [
                        'tags' => ['Routes'],
                        'summary' => 'Show a route stop with needed services',
                        'operationId' => 'showRouteStop',
                        'parameters' => [
                            [
                                'name' => 'routeStopId',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Route stop ID.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Route stop details.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/RouteStopShowResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/RouteStopNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/route/route-stops' => [
                    'get' => [
                        'tags' => ['Routes'],
                        'summary' => 'List unfulfilled route stops with needed services',
                        'operationId' => 'listUnfulfilledRouteStops',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'search',
                                'in' => 'query',
                                'required' => false,
                                'description' => 'Case-insensitive search term matched against route stop location and description.',
                                'schema' => [
                                    'type' => 'string',
                                    'maxLength' => 255,
                                    'example' => 'fuel',
                                ],
                            ],
                            [
                                'name' => 'page',
                                'in' => 'query',
                                'required' => false,
                                'description' => 'Page number.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                    'default' => 1,
                                ],
                            ],
                            [
                                'name' => 'per_page',
                                'in' => 'query',
                                'required' => false,
                                'description' => 'Number of route stops per page.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                    'maximum' => 100,
                                    'default' => 15,
                                ],
                            ],
                            [
                                'name' => 'sortBy.key',
                                'in' => 'query',
                                'required' => false,
                                'description' => 'Field to sort route stops by.',
                                'schema' => [
                                    'type' => 'string',
                                    'enum' => [
                                        'id',
                                        'route_id',
                                        'location',
                                        'description',
                                        'stop_at',
                                        'number_of_trucks',
                                        'number_of_drivers',
                                    ],
                                    'default' => 'stop_at',
                                ],
                            ],
                            [
                                'name' => 'sortBy.order',
                                'in' => 'query',
                                'required' => false,
                                'description' => 'Sort direction.',
                                'schema' => [
                                    'type' => 'string',
                                    'enum' => ['asc', 'desc'],
                                    'default' => 'desc',
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Unfulfilled route stop list.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/UnfulfilledRouteStopsIndexResponse',
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
                '/api/route/route-stops/{route_id}' => [
                    'get' => [
                        'tags' => ['Routes'],
                        'summary' => 'List route stops with needed services for a route',
                        'operationId' => 'listRouteStops',
                        'parameters' => [
                            [
                                'name' => 'route_id',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Route ID.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Route stop list.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/RouteStopsIndexResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/RouteNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/service' => [
                    'get' => [
                        'tags' => ['Services'],
                        'summary' => 'List services',
                        'operationId' => 'listServices',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Service list.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ServicesIndexResponse',
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
                    'post' => [
                        'tags' => ['Services'],
                        'summary' => 'Create a service',
                        'operationId' => 'createService',
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
                                        '$ref' => '#/components/schemas/ServiceCreateRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Service created successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ServiceCreateResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/AdminRoleRequired',
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
                '/api/service/{id}' => [
                    'get' => [
                        'tags' => ['Services'],
                        'summary' => 'Get a service',
                        'operationId' => 'getService',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Service ID.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Service details.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ServiceResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/AdminRoleRequired',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/ServiceNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Services'],
                        'summary' => 'Delete a service',
                        'operationId' => 'deleteService',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Service ID.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Service deleted successfully.',
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
                            '404' => [
                                '$ref' => '#/components/responses/ServiceNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
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
                '/api/admin/approve' => [
                    'get' => [
                        'tags' => ['Admin'],
                        'summary' => 'List dispatcher and rest stop profiles needing approval',
                        'operationId' => 'listProfilesNeedingApproval',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Profiles needing approval.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/PendingProfileApprovalsResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/AdminRoleRequired',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/admin/approve/{userId}' => [
                    'post' => [
                        'tags' => ['Admin'],
                        'summary' => 'Approve a dispatcher or rest stop profile',
                        'operationId' => 'approveProfile',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'userId',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'User ID for a dispatcher or rest stop profile.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Profile approved successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ProfileApprovalResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/AdminRoleRequired',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/ApprovableProfileNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/driver' => [
                    'get' => [
                        'tags' => ['Drivers'],
                        'summary' => 'Get the authenticated driver profile',
                        'operationId' => 'getAuthenticatedDriver',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Authenticated driver profile.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/CurrentDriverResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/DriverNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
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
                '/api/dispatcher/all' => [
                    'get' => [
                        'tags' => ['Dispatchers'],
                        'summary' => 'List dispatchers',
                        'operationId' => 'listDispatchers',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Dispatcher list.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/DispatchersIndexResponse',
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
                '/api/dispatcher' => [
                    'get' => [
                        'tags' => ['Dispatchers'],
                        'summary' => 'Get the authenticated dispatcher profile',
                        'operationId' => 'getAuthenticatedDispatcher',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Authenticated dispatcher profile.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/CurrentDispatcherResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/DispatcherNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Dispatchers'],
                        'summary' => 'Create or update the authenticated dispatcher profile',
                        'operationId' => 'upsertAuthenticatedDispatcher',
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
                                        '$ref' => '#/components/schemas/DispatcherUpsertRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Dispatcher profile updated successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/DispatcherUpsertResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '201' => [
                                'description' => 'Dispatcher profile created successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/DispatcherUpsertResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/DispatcherProfileForbidden',
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
                '/api/dispatcher/route' => [
                    'post' => [
                        'tags' => ['Routes'],
                        'summary' => 'Create a route for the authenticated dispatcher',
                        'operationId' => 'createDispatcherRoute',
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
                                        '$ref' => '#/components/schemas/DispatcherRouteCreateRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Route created successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/DispatcherRouteCreateResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/DispatcherRouteForbidden',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/DispatcherNotFound',
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
                '/api/dispatcher/route/route-stop' => [
                    'post' => [
                        'tags' => ['Routes'],
                        'summary' => 'Create a route stop for a route owned by the authenticated dispatcher',
                        'operationId' => 'createDispatcherRouteStop',
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
                                        '$ref' => '#/components/schemas/DispatcherRouteStopCreateRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Route stop created successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/DispatcherRouteStopCreateResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/DispatcherRouteStopForbidden',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/RouteNotFound',
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
                '/api/dispatcher/route/route-stop/{routeStopId}/services' => [
                    'put' => [
                        'tags' => ['Routes'],
                        'summary' => 'Synchronize services and quantities for a route stop owned by the authenticated dispatcher',
                        'operationId' => 'syncDispatcherRouteStopServices',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'routeStopId',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Route stop ID.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/DispatcherRouteStopServicesSyncRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Route stop services updated successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/DispatcherRouteStopServicesSyncResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/DispatcherRouteStopServicesForbidden',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/RouteStopNotFound',
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
                '/api/dispatcher/route/{dispatcherId}' => [
                    'get' => [
                        'tags' => ['Routes'],
                        'summary' => 'List routes for a dispatcher',
                        'operationId' => 'listDispatcherRoutes',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'dispatcherId',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Dispatcher ID.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Dispatcher route list.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/DispatcherRoutesIndexResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/DispatcherByIdNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/dispatcher/route/close/{routeId}' => [
                    'post' => [
                        'tags' => ['Routes'],
                        'summary' => 'Close a route owned by the authenticated dispatcher',
                        'operationId' => 'closeDispatcherRoute',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'routeId',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Route ID.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Route closed successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/DispatcherRouteCloseResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/DispatcherRouteCloseForbidden',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/RouteNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/rest-stop' => [
                    'get' => [
                        'tags' => ['Rest Stops'],
                        'summary' => 'Get the authenticated rest stop profile',
                        'operationId' => 'getAuthenticatedRestStop',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Authenticated rest stop profile.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/CurrentRestStopResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/RestStopNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                    'post' => [
                        'tags' => ['Rest Stops'],
                        'summary' => 'Create or update the authenticated rest stop profile',
                        'operationId' => 'upsertAuthenticatedRestStop',
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
                                        '$ref' => '#/components/schemas/RestStopUpsertRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Rest stop profile updated successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/RestStopUpsertResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '201' => [
                                'description' => 'Rest stop profile created successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/RestStopUpsertResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/RestStopProfileForbidden',
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
                '/api/rest-stop/services/add' => [
                    'post' => [
                        'tags' => ['Rest Stops'],
                        'summary' => 'Add a service to the authenticated rest stop',
                        'operationId' => 'addAuthenticatedRestStopService',
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
                                        '$ref' => '#/components/schemas/RestStopServiceStoreRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Rest stop service already exists.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/RestStopServiceStoreResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '201' => [
                                'description' => 'Rest stop service added successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/RestStopServiceStoreResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/RestStopServiceForbidden',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/RestStopNotFound',
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
                '/api/rest-stop/bids' => [
                    'post' => [
                        'tags' => ['Rest Stops'],
                        'summary' => 'Create a bid for the authenticated rest stop',
                        'operationId' => 'createAuthenticatedRestStopBid',
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
                                        '$ref' => '#/components/schemas/BidStoreRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Bid updated successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/BidStoreResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '201' => [
                                'description' => 'Bid created successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/BidStoreResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/BidForbidden',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/BidNotFound',
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
                '/api/rest-stop/bids/{routeStopId}' => [
                    'get' => [
                        'tags' => ['Rest Stops'],
                        'summary' => 'Show the authenticated rest stop bid for a route stop',
                        'operationId' => 'showAuthenticatedRestStopBid',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'routeStopId',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Route stop ID for the bid.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Bid details.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/BidShowResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/BidViewForbidden',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/BidNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                    'delete' => [
                        'tags' => ['Rest Stops'],
                        'summary' => 'Delete the authenticated rest stop bid for a route stop',
                        'operationId' => 'deleteAuthenticatedRestStopBid',
                        'security' => [
                            [
                                'sanctumBearer' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'routeStopId',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Route stop ID for the bid.',
                                'schema' => [
                                    'type' => 'integer',
                                    'minimum' => 1,
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Bid deleted successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/BidDeleteResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/BidDeleteForbidden',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/BidNotFound',
                            ],
                            '500' => [
                                '$ref' => '#/components/responses/ServerError',
                            ],
                        ],
                    ],
                ],
                '/api/rest-stop/services/remove' => [
                    'post' => [
                        'tags' => ['Rest Stops'],
                        'summary' => 'Remove a service from the authenticated rest stop',
                        'operationId' => 'removeAuthenticatedRestStopService',
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
                                        '$ref' => '#/components/schemas/RestStopServiceRemoveRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Rest stop service removed successfully.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/RestStopServiceRemoveResponse',
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                '$ref' => '#/components/responses/Unauthenticated',
                            ],
                            '403' => [
                                '$ref' => '#/components/responses/RestStopServiceRemoveForbidden',
                            ],
                            '404' => [
                                '$ref' => '#/components/responses/RestStopServiceRemoveNotFound',
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
                    'Service' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'name',
                            'measurement_unit',
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'name' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Tire replacement',
                            ],
                            'measurement_unit' => [
                                'type' => 'string',
                                'nullable' => true,
                                'maxLength' => 255,
                                'example' => 'piece',
                            ],
                        ],
                    ],
                    'RestStopServiceListItem' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'name',
                            'measurement_unit',
                            'price_per_unit',
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'name' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Tire replacement',
                            ],
                            'measurement_unit' => [
                                'type' => 'string',
                                'nullable' => true,
                                'maxLength' => 255,
                                'example' => 'piece',
                            ],
                            'price_per_unit' => [
                                'type' => 'string',
                                'description' => 'Decimal unit price stored with the rest stop service.',
                                'pattern' => '^\\d+(\\.\\d{2})$',
                                'example' => '12.50',
                            ],
                        ],
                    ],
                    'ServicesIndexResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['services'],
                                'properties' => [
                                    'services' => [
                                        'type' => 'array',
                                        'items' => [
                                            '$ref' => '#/components/schemas/Service',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'RestStopServicesIndexResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['services'],
                                'properties' => [
                                    'services' => [
                                        'type' => 'array',
                                        'items' => [
                                            '$ref' => '#/components/schemas/RestStopServiceListItem',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'RouteShowResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['route'],
                                'properties' => [
                                    'route' => [
                                        '$ref' => '#/components/schemas/DispatcherRouteWithStops',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'RouteStopShowResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['route_stop'],
                                'properties' => [
                                    'route_stop' => [
                                        '$ref' => '#/components/schemas/DispatcherRouteStop',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'RouteStopsIndexResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['route_stops'],
                                'properties' => [
                                    'route_stops' => [
                                        'type' => 'array',
                                        'items' => [
                                            '$ref' => '#/components/schemas/DispatcherRouteStop',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'UnfulfilledRouteStopsIndexResponse' => [
                        'type' => 'object',
                        'required' => ['data', 'links', 'meta'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['route_stops'],
                                'properties' => [
                                    'route_stops' => [
                                        'type' => 'array',
                                        'items' => [
                                            '$ref' => '#/components/schemas/UnfulfilledRouteStop',
                                        ],
                                    ],
                                ],
                            ],
                            'links' => [
                                'type' => 'object',
                                'required' => ['first', 'last', 'prev', 'next'],
                                'properties' => [
                                    'first' => [
                                        'type' => 'string',
                                        'format' => 'uri',
                                        'example' => 'http://localhost/api/route/route-stops?page=1',
                                    ],
                                    'last' => [
                                        'type' => 'string',
                                        'format' => 'uri',
                                        'example' => 'http://localhost/api/route/route-stops?page=3',
                                    ],
                                    'prev' => [
                                        'type' => 'string',
                                        'format' => 'uri',
                                        'nullable' => true,
                                        'example' => null,
                                    ],
                                    'next' => [
                                        'type' => 'string',
                                        'format' => 'uri',
                                        'nullable' => true,
                                        'example' => 'http://localhost/api/route/route-stops?page=2',
                                    ],
                                ],
                            ],
                            'meta' => [
                                'type' => 'object',
                                'required' => [
                                    'current_page',
                                    'from',
                                    'last_page',
                                    'path',
                                    'per_page',
                                    'to',
                                    'total',
                                ],
                                'properties' => [
                                    'current_page' => [
                                        'type' => 'integer',
                                        'minimum' => 1,
                                        'example' => 1,
                                    ],
                                    'from' => [
                                        'type' => 'integer',
                                        'nullable' => true,
                                        'example' => 1,
                                    ],
                                    'last_page' => [
                                        'type' => 'integer',
                                        'minimum' => 1,
                                        'example' => 3,
                                    ],
                                    'path' => [
                                        'type' => 'string',
                                        'format' => 'uri',
                                        'example' => 'http://localhost/api/route/route-stops',
                                    ],
                                    'per_page' => [
                                        'type' => 'integer',
                                        'minimum' => 1,
                                        'maximum' => 100,
                                        'example' => 15,
                                    ],
                                    'to' => [
                                        'type' => 'integer',
                                        'nullable' => true,
                                        'example' => 15,
                                    ],
                                    'total' => [
                                        'type' => 'integer',
                                        'minimum' => 0,
                                        'example' => 35,
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
                    'CurrentUser' => [
                        'allOf' => [
                            [
                                '$ref' => '#/components/schemas/User',
                            ],
                            [
                                'type' => 'object',
                                'required' => ['roles'],
                                'properties' => [
                                    'roles' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                        'example' => ['admin'],
                                    ],
                                ],
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
                    'Dispatcher' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'user_id',
                            'company_name',
                            'city',
                            'address',
                            'post_code',
                            'registration_number',
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
                            'company_name' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Acme Dispatch',
                            ],
                            'city' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Belgrade',
                            ],
                            'address' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Main Street 1',
                            ],
                            'post_code' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => '11000',
                            ],
                            'registration_number' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'REG-1234',
                            ],
                        ],
                    ],
                    'DispatcherRoute' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'dispatcher_id',
                            'origin',
                            'destination',
                            'planned_travel_details',
                            'convoy_size',
                            'start_date',
                            'end_date',
                            'closed_at',
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'dispatcher_id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'origin' => [
                                'type' => 'string',
                                'example' => 'Belgrade warehouse',
                            ],
                            'destination' => [
                                'type' => 'string',
                                'example' => 'Berlin logistics hub',
                            ],
                            'planned_travel_details' => [
                                'type' => 'string',
                                'nullable' => true,
                                'example' => 'Take the A3 corridor and stop near Vienna.',
                            ],
                            'convoy_size' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'example' => 3,
                            ],
                            'start_date' => [
                                'type' => 'string',
                                'format' => 'date',
                                'example' => '2026-10-01',
                            ],
                            'end_date' => [
                                'type' => 'string',
                                'format' => 'date',
                                'example' => '2026-10-05',
                            ],
                            'closed_at' => [
                                'type' => 'string',
                                'format' => 'date-time',
                                'nullable' => true,
                                'example' => null,
                            ],
                        ],
                    ],
                    'DispatcherRouteStopService' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'name',
                            'measurement_unit',
                            'quantity',
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'name' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Fuel',
                            ],
                            'measurement_unit' => [
                                'type' => 'string',
                                'nullable' => true,
                                'maxLength' => 255,
                                'example' => 'liter',
                            ],
                            'quantity' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'example' => 200,
                            ],
                        ],
                    ],
                    'DispatcherRouteStop' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'route_id',
                            'location',
                            'description',
                            'stop_at',
                            'fulfiled_at',
                            'fulfiled_by',
                            'number_of_trucks',
                            'number_of_drivers',
                            'bids_count',
                            'services',
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'route_id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'location' => [
                                'type' => 'string',
                                'nullable' => true,
                                'maxLength' => 255,
                                'example' => 'Vienna fuel stop',
                            ],
                            'description' => [
                                'type' => 'string',
                                'nullable' => true,
                                'example' => 'Refuel and inspect tires before crossing into Germany.',
                            ],
                            'stop_at' => [
                                'type' => 'string',
                                'format' => 'date-time',
                                'example' => '2026-10-02T10:30:00Z',
                            ],
                            'fulfiled_at' => [
                                'type' => 'string',
                                'format' => 'date-time',
                                'nullable' => true,
                                'example' => null,
                            ],
                            'fulfiled_by' => [
                                'type' => 'integer',
                                'nullable' => true,
                                'description' => 'Rest stop ID that fulfilled this route stop.',
                                'example' => null,
                            ],
                            'number_of_trucks' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'example' => 3,
                            ],
                            'number_of_drivers' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'example' => 4,
                            ],
                            'bids_count' => [
                                'type' => 'integer',
                                'minimum' => 0,
                                'description' => 'Number of bids submitted for this route stop.',
                                'example' => 2,
                            ],
                            'services' => [
                                'type' => 'array',
                                'items' => [
                                    '$ref' => '#/components/schemas/DispatcherRouteStopService',
                                ],
                            ],
                        ],
                    ],
                    'UnfulfilledRouteStop' => [
                        'allOf' => [
                            [
                                '$ref' => '#/components/schemas/DispatcherRouteStop',
                            ],
                            [
                                'type' => 'object',
                                'required' => ['dispatcher_company_name'],
                                'properties' => [
                                    'dispatcher_company_name' => [
                                        'type' => 'string',
                                        'nullable' => true,
                                        'description' => 'Company name for the dispatcher that created the route this stop belongs to.',
                                        'example' => 'Acme Dispatch',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'DispatcherRouteWithStops' => [
                        'allOf' => [
                            [
                                '$ref' => '#/components/schemas/DispatcherRoute',
                            ],
                            [
                                'type' => 'object',
                                'required' => ['route_stops'],
                                'properties' => [
                                    'route_stops' => [
                                        'type' => 'array',
                                        'items' => [
                                            '$ref' => '#/components/schemas/DispatcherRouteStop',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'RestStop' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'user_id',
                            'city',
                            'address',
                            'post_code',
                            'works_from',
                            'works_to',
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
                            'city' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Belgrade',
                            ],
                            'address' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Highway 1',
                            ],
                            'post_code' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => '11000',
                            ],
                            'works_from' => [
                                'type' => 'string',
                                'pattern' => '^\\d{2}:\\d{2}$',
                                'example' => '08:00',
                            ],
                            'works_to' => [
                                'type' => 'string',
                                'pattern' => '^\\d{2}:\\d{2}$',
                                'example' => '22:00',
                            ],
                        ],
                    ],
                    'PendingDispatcherApproval' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'user_id',
                            'company_name',
                            'city',
                            'address',
                            'post_code',
                            'registration_number',
                            'is_approved',
                            'user',
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
                            'company_name' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Acme Dispatch',
                            ],
                            'city' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Belgrade',
                            ],
                            'address' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Main Street 1',
                            ],
                            'post_code' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => '11000',
                            ],
                            'registration_number' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'REG-1234',
                            ],
                            'is_approved' => [
                                'type' => 'boolean',
                                'example' => false,
                            ],
                            'user' => [
                                '$ref' => '#/components/schemas/User',
                            ],
                        ],
                    ],
                    'PendingRestStopApproval' => [
                        'type' => 'object',
                        'required' => [
                            'id',
                            'user_id',
                            'city',
                            'address',
                            'post_code',
                            'works_from',
                            'works_to',
                            'is_approved',
                            'user',
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
                            'city' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Belgrade',
                            ],
                            'address' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => 'Highway 1',
                            ],
                            'post_code' => [
                                'type' => 'string',
                                'maxLength' => 255,
                                'example' => '11000',
                            ],
                            'works_from' => [
                                'type' => 'string',
                                'pattern' => '^\\d{2}:\\d{2}$',
                                'example' => '08:00',
                            ],
                            'works_to' => [
                                'type' => 'string',
                                'pattern' => '^\\d{2}:\\d{2}$',
                                'example' => '22:00',
                            ],
                            'is_approved' => [
                                'type' => 'boolean',
                                'example' => false,
                            ],
                            'user' => [
                                '$ref' => '#/components/schemas/User',
                            ],
                        ],
                    ],
                    'ProfileApproval' => [
                        'type' => 'object',
                        'required' => [
                            'profile_id',
                            'user_id',
                            'profile_type',
                            'is_approved',
                            'user',
                        ],
                        'properties' => [
                            'profile_id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'user_id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'profile_type' => [
                                'type' => 'string',
                                'enum' => ['dispatcher', 'rest_stop'],
                                'example' => 'dispatcher',
                            ],
                            'is_approved' => [
                                'type' => 'boolean',
                                'example' => true,
                            ],
                            'user' => [
                                '$ref' => '#/components/schemas/User',
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
                    'RestStopService' => [
                        'type' => 'object',
                        'required' => [
                            'rest_stop_id',
                            'service_id',
                            'price_per_unit',
                        ],
                        'properties' => [
                            'rest_stop_id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'service_id' => [
                                'type' => 'integer',
                                'example' => 2,
                            ],
                            'price_per_unit' => [
                                'type' => 'string',
                                'description' => 'Decimal unit price stored with the rest stop service.',
                                'pattern' => '^\\d+(\\.\\d{2})$',
                                'example' => '12.50',
                            ],
                        ],
                    ],
                    'Bid' => [
                        'type' => 'object',
                        'required' => [
                            'route_stop_id',
                            'rest_stop_id',
                            'original_price',
                            'price',
                        ],
                        'properties' => [
                            'route_stop_id' => [
                                'type' => 'integer',
                                'example' => 1,
                            ],
                            'rest_stop_id' => [
                                'type' => 'integer',
                                'description' => 'Resolved from the authenticated rest stop user.',
                                'example' => 2,
                            ],
                            'original_price' => [
                                'type' => 'string',
                                'description' => 'Original bid price stored with two decimal places.',
                                'pattern' => '^\\d+(\\.\\d{2})$',
                                'example' => '300.00',
                            ],
                            'price' => [
                                'type' => 'string',
                                'description' => 'Bid price stored with two decimal places.',
                                'pattern' => '^\\d+(\\.\\d{2})$',
                                'example' => '250.75',
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
                    'DispatcherUpsertRequest' => [
                        'type' => 'object',
                        'required' => [
                            'company_name',
                            'city',
                            'address',
                            'post_code',
                            'registration_number',
                        ],
                        'properties' => [
                            'company_name' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'Acme Dispatch',
                            ],
                            'city' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'Belgrade',
                            ],
                            'address' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'Main Street 1',
                            ],
                            'post_code' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => '11000',
                            ],
                            'registration_number' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'REG-1234',
                            ],
                        ],
                    ],
                    'DispatcherRouteCreateRequest' => [
                        'type' => 'object',
                        'required' => [
                            'origin',
                            'destination',
                            'convoy_size',
                            'start_date',
                            'end_date',
                        ],
                        'properties' => [
                            'origin' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'example' => 'Belgrade warehouse',
                            ],
                            'destination' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'example' => 'Berlin logistics hub',
                            ],
                            'planned_travel_details' => [
                                'type' => 'string',
                                'nullable' => true,
                                'example' => 'Take the A3 corridor and stop near Vienna.',
                            ],
                            'convoy_size' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'example' => 3,
                            ],
                            'start_date' => [
                                'type' => 'string',
                                'format' => 'date',
                                'description' => 'Must be a future date.',
                                'example' => '2026-10-01',
                            ],
                            'end_date' => [
                                'type' => 'string',
                                'format' => 'date',
                                'description' => 'Must be a future date that is the same as or after start_date.',
                                'example' => '2026-10-05',
                            ],
                        ],
                    ],
                    'DispatcherRouteStopCreateRequest' => [
                        'type' => 'object',
                        'required' => [
                            'route_id',
                            'location',
                            'stop_at',
                            'number_of_trucks',
                            'number_of_drivers',
                            'services',
                        ],
                        'properties' => [
                            'route_id' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'description' => 'Route ID owned by the authenticated dispatcher.',
                                'example' => 1,
                            ],
                            'location' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'Vienna fuel stop',
                            ],
                            'description' => [
                                'type' => 'string',
                                'nullable' => true,
                                'example' => 'Refuel and inspect tires before crossing into Germany.',
                            ],
                            'stop_at' => [
                                'type' => 'string',
                                'format' => 'date-time',
                                'description' => 'Must be in the future.',
                                'example' => '2026-10-02T10:30:00Z',
                            ],
                            'number_of_trucks' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'example' => 3,
                            ],
                            'number_of_drivers' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'example' => 4,
                            ],
                            'services' => [
                                'type' => 'array',
                                'minItems' => 1,
                                'items' => [
                                    'type' => 'object',
                                    'required' => [
                                        'service_id',
                                        'quantity',
                                    ],
                                    'properties' => [
                                        'service_id' => [
                                            'type' => 'integer',
                                            'minimum' => 1,
                                            'description' => 'Existing service ID. Each service_id must be unique in the request.',
                                            'example' => 2,
                                        ],
                                        'quantity' => [
                                            'type' => 'integer',
                                            'minimum' => 1,
                                            'example' => 200,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'DispatcherRouteStopServicesSyncRequest' => [
                        'type' => 'object',
                        'required' => [
                            'services',
                        ],
                        'properties' => [
                            'services' => [
                                'type' => 'array',
                                'minItems' => 1,
                                'description' => 'Full desired service list for the route stop. Omitted existing services are removed. Existing services are updated with the provided quantity.',
                                'items' => [
                                    'type' => 'object',
                                    'required' => [
                                        'service_id',
                                        'quantity',
                                    ],
                                    'properties' => [
                                        'service_id' => [
                                            'type' => 'integer',
                                            'minimum' => 1,
                                            'description' => 'Existing service ID. Each service_id must be unique in the request.',
                                            'example' => 2,
                                        ],
                                        'quantity' => [
                                            'type' => 'integer',
                                            'minimum' => 1,
                                            'example' => 200,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'RestStopUpsertRequest' => [
                        'type' => 'object',
                        'required' => [
                            'city',
                            'address',
                            'post_code',
                            'works_from',
                            'works_to',
                        ],
                        'properties' => [
                            'city' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'Belgrade',
                            ],
                            'address' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'Highway 1',
                            ],
                            'post_code' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => '11000',
                            ],
                            'works_from' => [
                                'type' => 'string',
                                'description' => 'Opening time in 24-hour HH:mm format.',
                                'pattern' => '^\\d{2}:\\d{2}$',
                                'example' => '08:00',
                            ],
                            'works_to' => [
                                'type' => 'string',
                                'description' => 'Closing time in 24-hour HH:mm format.',
                                'pattern' => '^\\d{2}:\\d{2}$',
                                'example' => '22:00',
                            ],
                        ],
                    ],
                    'ServiceCreateRequest' => [
                        'type' => 'object',
                        'required' => [
                            'name',
                        ],
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'description' => 'Must be unique.',
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'Tire replacement',
                            ],
                            'measurement_unit' => [
                                'type' => 'string',
                                'nullable' => true,
                                'minLength' => 1,
                                'maxLength' => 255,
                                'example' => 'piece',
                            ],
                        ],
                    ],
                    'RestStopServiceStoreRequest' => [
                        'type' => 'object',
                        'required' => [
                            'service_id',
                            'price_per_unit',
                        ],
                        'properties' => [
                            'service_id' => [
                                'type' => 'integer',
                                'description' => 'Existing service ID.',
                                'minimum' => 1,
                                'example' => 2,
                            ],
                            'price_per_unit' => [
                                'type' => 'string',
                                'description' => 'Non-negative decimal unit price with up to two decimal places.',
                                'pattern' => '^\\d+(\\.\\d{1,2})?$',
                                'example' => '12.50',
                            ],
                        ],
                    ],
                    'BidStoreRequest' => [
                        'type' => 'object',
                        'required' => [
                            'route_stop_id',
                            'original_price',
                            'price',
                        ],
                        'properties' => [
                            'route_stop_id' => [
                                'type' => 'integer',
                                'description' => 'Existing route stop ID.',
                                'minimum' => 1,
                                'example' => 1,
                            ],
                            'original_price' => [
                                'type' => 'string',
                                'description' => 'Non-negative decimal original price with exactly two decimal places.',
                                'pattern' => '^\\d+\\.\\d{2}$',
                                'example' => '300.00',
                            ],
                            'price' => [
                                'type' => 'string',
                                'description' => 'Non-negative decimal bid price with exactly two decimal places.',
                                'pattern' => '^\\d+\\.\\d{2}$',
                                'example' => '250.75',
                            ],
                        ],
                    ],
                    'RestStopServiceRemoveRequest' => [
                        'type' => 'object',
                        'required' => [
                            'service_id',
                        ],
                        'properties' => [
                            'service_id' => [
                                'type' => 'integer',
                                'description' => 'Existing service ID.',
                                'minimum' => 1,
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
                                        '$ref' => '#/components/schemas/CurrentUser',
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
                    'CurrentDriverResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
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
                    'CurrentDispatcherResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['dispatcher'],
                                'properties' => [
                                    'dispatcher' => [
                                        '$ref' => '#/components/schemas/Dispatcher',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'CurrentRestStopResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['rest_stop'],
                                'properties' => [
                                    'rest_stop' => [
                                        '$ref' => '#/components/schemas/RestStop',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'DispatchersIndexResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['dispatchers'],
                                'properties' => [
                                    'dispatchers' => [
                                        'type' => 'array',
                                        'items' => [
                                            '$ref' => '#/components/schemas/Dispatcher',
                                        ],
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
                    'DispatcherUpsertResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Dispatcher profile created successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['dispatcher'],
                                'properties' => [
                                    'dispatcher' => [
                                        '$ref' => '#/components/schemas/Dispatcher',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'DispatcherRouteCreateResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Route created successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['route'],
                                'properties' => [
                                    'route' => [
                                        '$ref' => '#/components/schemas/DispatcherRoute',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'DispatcherRoutesIndexResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['routes'],
                                'properties' => [
                                    'routes' => [
                                        'type' => 'array',
                                        'items' => [
                                            '$ref' => '#/components/schemas/DispatcherRoute',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'DispatcherRouteCloseResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Route closed successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['route'],
                                'properties' => [
                                    'route' => [
                                        '$ref' => '#/components/schemas/DispatcherRoute',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'DispatcherRouteStopCreateResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Route stop created successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['route_stop'],
                                'properties' => [
                                    'route_stop' => [
                                        '$ref' => '#/components/schemas/DispatcherRouteStop',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'DispatcherRouteStopServicesSyncResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Route stop services updated successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['route_stop'],
                                'properties' => [
                                    'route_stop' => [
                                        '$ref' => '#/components/schemas/DispatcherRouteStop',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'RestStopUpsertResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Rest stop profile created successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['rest_stop'],
                                'properties' => [
                                    'rest_stop' => [
                                        '$ref' => '#/components/schemas/RestStop',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'ProfileApprovalResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Profile approved successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['approval'],
                                'properties' => [
                                    'approval' => [
                                        '$ref' => '#/components/schemas/ProfileApproval',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'PendingProfileApprovalsResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => [
                                    'dispatchers',
                                    'rest_stops',
                                ],
                                'properties' => [
                                    'dispatchers' => [
                                        'type' => 'array',
                                        'items' => [
                                            '$ref' => '#/components/schemas/PendingDispatcherApproval',
                                        ],
                                    ],
                                    'rest_stops' => [
                                        'type' => 'array',
                                        'items' => [
                                            '$ref' => '#/components/schemas/PendingRestStopApproval',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'RestStopServiceStoreResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Rest stop service added successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['rest_stop_service'],
                                'properties' => [
                                    'rest_stop_service' => [
                                        '$ref' => '#/components/schemas/RestStopService',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'BidStoreResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Bid created successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['bid'],
                                'properties' => [
                                    'bid' => [
                                        '$ref' => '#/components/schemas/Bid',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'BidShowResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['bid'],
                                'properties' => [
                                    'bid' => [
                                        '$ref' => '#/components/schemas/Bid',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'BidDeleteResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Bid deleted successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['bid'],
                                'properties' => [
                                    'bid' => [
                                        '$ref' => '#/components/schemas/Bid',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'RestStopServiceRemoveResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Rest stop service removed successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['rest_stop_service'],
                                'properties' => [
                                    'rest_stop_service' => [
                                        '$ref' => '#/components/schemas/RestStopService',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'ServiceResponse' => [
                        'type' => 'object',
                        'required' => ['data'],
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'required' => ['service'],
                                'properties' => [
                                    'service' => [
                                        '$ref' => '#/components/schemas/Service',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'ServiceCreateResponse' => [
                        'type' => 'object',
                        'required' => [
                            'message',
                            'data',
                        ],
                        'properties' => [
                            'message' => [
                                'type' => 'string',
                                'example' => 'Service created successfully.',
                            ],
                            'data' => [
                                'type' => 'object',
                                'required' => ['service'],
                                'properties' => [
                                    'service' => [
                                        '$ref' => '#/components/schemas/Service',
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
                    'DriverNotFound' => [
                        'description' => 'Driver profile not found for the authenticated user.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Driver profile not found.',
                                ],
                            ],
                        ],
                    ],
                    'DispatcherNotFound' => [
                        'description' => 'Dispatcher profile not found for the authenticated user.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Dispatcher profile not found.',
                                ],
                            ],
                        ],
                    ],
                    'DispatcherByIdNotFound' => [
                        'description' => 'Dispatcher not found.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Dispatcher not found.',
                                ],
                            ],
                        ],
                    ],
                    'RestStopNotFound' => [
                        'description' => 'Rest stop profile not found for the authenticated user.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Rest stop profile not found.',
                                ],
                            ],
                        ],
                    ],
                    'RestStopByIdNotFound' => [
                        'description' => 'Rest stop not found.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Rest stop not found.',
                                ],
                            ],
                        ],
                    ],
                    'ServiceNotFound' => [
                        'description' => 'Service not found.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Service not found.',
                                ],
                            ],
                        ],
                    ],
                    'ApprovableProfileNotFound' => [
                        'description' => 'No dispatcher or rest stop profile exists for the user ID.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Approvable profile not found.',
                                ],
                            ],
                        ],
                    ],
                    'RouteNotFound' => [
                        'description' => 'Route not found for the authenticated dispatcher.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Route not found.',
                                ],
                            ],
                        ],
                    ],
                    'RouteStopNotFound' => [
                        'description' => 'Route stop not found.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Route stop not found.',
                                ],
                            ],
                        ],
                    ],
                    'AdminRoleRequired' => [
                        'description' => 'The authenticated user does not have the admin role.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'User does not have the right roles.',
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
                    'DispatcherProfileForbidden' => [
                        'description' => 'The authenticated user is not a dispatcher.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Only dispatcher users can create or update dispatcher profiles.',
                                ],
                            ],
                        ],
                    ],
                    'DispatcherRouteForbidden' => [
                        'description' => 'The authenticated user is not a dispatcher.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Only dispatcher users can create routes.',
                                ],
                            ],
                        ],
                    ],
                    'DispatcherRouteCloseForbidden' => [
                        'description' => 'The authenticated user is not a dispatcher.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Only dispatcher users can close routes.',
                                ],
                            ],
                        ],
                    ],
                    'DispatcherRouteStopForbidden' => [
                        'description' => 'The authenticated user is not a dispatcher, or the route belongs to another dispatcher.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'examples' => [
                                    'non_dispatcher' => [
                                        'summary' => 'Authenticated user is not a dispatcher',
                                        'value' => [
                                            'message' => 'Only dispatcher users can create route stops.',
                                        ],
                                    ],
                                    'route_owner' => [
                                        'summary' => 'Route belongs to another dispatcher',
                                        'value' => [
                                            'message' => 'You cannot add route stops to a route you did not create.',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'DispatcherRouteStopServicesForbidden' => [
                        'description' => 'The authenticated user is not a dispatcher, or the route stop belongs to a route created by another dispatcher.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'examples' => [
                                    'non_dispatcher' => [
                                        'summary' => 'Authenticated user is not a dispatcher',
                                        'value' => [
                                            'message' => 'Only dispatcher users can update route stop services.',
                                        ],
                                    ],
                                    'route_owner' => [
                                        'summary' => 'Route stop belongs to another dispatcher route',
                                        'value' => [
                                            'message' => 'You cannot update route stop services for a route you did not create.',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'RestStopProfileForbidden' => [
                        'description' => 'The authenticated user is not a rest stop.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Only rest stop users can create or update rest stop profiles.',
                                ],
                            ],
                        ],
                    ],
                    'RestStopServiceForbidden' => [
                        'description' => 'The authenticated user is not a rest stop.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Only rest stop users can add rest stop services.',
                                ],
                            ],
                        ],
                    ],
                    'BidForbidden' => [
                        'description' => 'The authenticated user is not a rest stop.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Only rest stop users can create bids.',
                                ],
                            ],
                        ],
                    ],
                    'BidViewForbidden' => [
                        'description' => 'The authenticated user is not a rest stop.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Only rest stop users can view bids.',
                                ],
                            ],
                        ],
                    ],
                    'BidDeleteForbidden' => [
                        'description' => 'The authenticated user is not a rest stop.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Only rest stop users can delete bids.',
                                ],
                            ],
                        ],
                    ],
                    'BidNotFound' => [
                        'description' => 'The rest stop profile, route stop, or bid was not found.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'examples' => [
                                    'rest_stop_profile' => [
                                        'summary' => 'Missing rest stop profile',
                                        'value' => [
                                            'message' => 'Rest stop profile not found.',
                                        ],
                                    ],
                                    'route_stop' => [
                                        'summary' => 'Missing route stop',
                                        'value' => [
                                            'message' => 'Route stop not found.',
                                        ],
                                    ],
                                    'bid' => [
                                        'summary' => 'Missing bid for authenticated rest stop',
                                        'value' => [
                                            'message' => 'Bid not found.',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'RestStopServiceRemoveForbidden' => [
                        'description' => 'The authenticated user is not a rest stop.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'example' => [
                                    'message' => 'Only rest stop users can remove rest stop services.',
                                ],
                            ],
                        ],
                    ],
                    'RestStopServiceRemoveNotFound' => [
                        'description' => 'The rest stop profile or rest stop service was not found.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorResponse',
                                ],
                                'examples' => [
                                    'rest_stop_profile' => [
                                        'summary' => 'Missing rest stop profile',
                                        'value' => [
                                            'message' => 'Rest stop profile not found.',
                                        ],
                                    ],
                                    'rest_stop_service' => [
                                        'summary' => 'Missing rest stop service',
                                        'value' => [
                                            'message' => 'Rest stop service not found.',
                                        ],
                                    ],
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
