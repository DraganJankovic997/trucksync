<?php

use App\OpenApi\OpenApiSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/documentation', function () {
    return view('swagger-ui');
})->name('swagger.ui');

Route::get('/api/documentation/openapi.json', function (Request $request) {
    return response()->json(OpenApiSpec::make($request->getSchemeAndHttpHost()));
})->name('swagger.openapi');
