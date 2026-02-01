<?php

namespace App\Http\Controllers\Api;

use App\Events\AlertCreated;
use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AlertController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bin_id' => ['required', 'integer', 'exists:bins,bin_id'],
            'timestamp' => ['nullable', 'date'],
            'type' => ['required', 'string', 'max:64'],
            'description' => ['required', 'string', 'max:255'],
            'is_resolved' => ['nullable', 'boolean'],
        ]);

        $alert = Alert::create([
            'bin_id' => $data['bin_id'],
            'timestamp' => isset($data['timestamp'])
                ? Carbon::parse($data['timestamp'])
                : now(),
            'type' => $data['type'],
            'description' => $data['description'],
            'is_resolved' => $data['is_resolved'] ?? false,
        ]);

        event(new AlertCreated($alert));

        return response()->json([
            'status' => 'created',
            'alert_id' => $alert->alert_id,
        ], 201);
    }
}
