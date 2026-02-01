<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataTransmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DataTransmissionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sensor_id' => ['required', 'integer', 'exists:sensors,sensor_id'],
            'timestamp' => ['nullable', 'date'],
            'successful' => ['required', 'boolean'],
            'error_message' => ['nullable', 'string', 'max:255'],
        ]);

        $transmission = DataTransmission::create([
            'sensor_id' => $data['sensor_id'],
            'timestamp' => isset($data['timestamp'])
                ? Carbon::parse($data['timestamp'])
                : now(),
            'successful' => $data['successful'],
            'error_message' => $data['error_message'] ?? null,
        ]);

        return response()->json([
            'status' => 'created',
            'transmission_id' => $transmission->transmission_id,
        ], 201);
    }
}
