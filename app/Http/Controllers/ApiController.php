<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected function dispatch($data, int $status = 200, array $headers = []): JsonResponse {
        $response = [
            'data' => $data,
            'success' => $status === 200 || $status === 201,
        ];

        return response()->json($response, $status, $headers);
    }
}
