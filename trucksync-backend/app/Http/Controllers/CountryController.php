<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'data' => [
                    'countries' => Country::query()
                        ->orderBy('name')
                        ->get(['code', 'name']),
                ],
            ]);
        } catch (\Throwable $throwable) {
            logger()->error('Unable to fetch countries.', [
                'exception' => $throwable,
            ]);

            return response()->json([
                'message' => 'Unable to fetch countries.',
            ], 500);
        }
    }
}
