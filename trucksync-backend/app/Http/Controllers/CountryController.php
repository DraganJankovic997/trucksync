<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'countries' => Country::query()
                    ->orderBy('name')
                    ->get(['code', 'name']),
            ],
        ]);
    }
}
