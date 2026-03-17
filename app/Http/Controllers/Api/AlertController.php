<?php

namespace App\Http\Controllers\Api;

use App\Events\AlertCreated;
use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
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
            'severity' => ['nullable', 'in:info,warning,critical'],
            'status' => ['nullable', 'in:open,acknowledged,resolved'],
            'is_resolved' => ['nullable', 'boolean'],
        ]);

        $timestamp = isset($data['timestamp'])
            ? Carbon::parse($data['timestamp'])
            : now();

        $status = $data['status']
            ?? (($data['is_resolved'] ?? false) ? Alert::STATUS_RESOLVED : Alert::STATUS_OPEN);

        $alert = Alert::create([
            'bin_id' => $data['bin_id'],
            'timestamp' => $timestamp,
            'type' => $data['type'],
            'description' => $data['description'],
            'severity' => $data['severity'] ?? Alert::SEVERITY_WARNING,
            'status' => $status,
            'last_seen_at' => $timestamp,
            'is_resolved' => $data['is_resolved'] ?? false,
        ]);

        $alert->logActivity('opened', 'Alert created through API endpoint.');

        event(new AlertCreated($alert));

        $admins = User::all();
        FilamentNotification::make()
            ->title('New Alert: ' . $alert->type)
            ->body($alert->description)
            ->danger()
            ->sendToDatabase($admins);

        return response()->json([
            'status' => 'created',
            'alert_id' => $alert->alert_id,
        ], 201);
    }
}
