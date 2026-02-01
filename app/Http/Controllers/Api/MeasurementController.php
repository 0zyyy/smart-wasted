<?php

namespace App\Http\Controllers\Api;

use App\Events\MeasurementCreated;
use App\Http\Controllers\Controller;
use App\Models\Measurement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MeasurementController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sensor_id' => ['required', 'integer', 'exists:sensors,sensor_id'],
            'timestamp' => ['nullable', 'date'],
            'value' => ['required', 'numeric'],
            'unit' => ['required', 'string', 'max:32'],
        ]);

        $measurement = Measurement::create([
            'sensor_id' => $data['sensor_id'],
            'timestamp' => isset($data['timestamp'])
                ? Carbon::parse($data['timestamp'])
                : now(),
            'value' => $data['value'],
            'unit' => $data['unit'],
        ]);

        event(new MeasurementCreated($measurement));

        return response()->json([
            'status' => 'created',
            'measurement_id' => $measurement->measurement_id,
        ], 201);
    }
}
