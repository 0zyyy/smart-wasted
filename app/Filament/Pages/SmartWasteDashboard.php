<?php

namespace App\Filament\Pages;

use App\Models\Alert;
use App\Models\AnalysisResult;
use App\Models\Bin;
use App\Models\CollectionSchedule;
use App\Models\DataTransmission;
use App\Models\Location;
use App\Models\MaintenanceLog;
use App\Models\Measurement;
use App\Models\Sensor;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class SmartWasteDashboard extends Page
{
    protected static ?string $title = 'Smart Waste Operations';
    protected static ?string $navigationLabel = 'Smart Waste';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bolt';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'dashboard';

    protected string $view = 'filament.pages.smart-waste-dashboard';

    protected ?string $heading = '';
    protected ?string $subheading = '';

    public int $refreshSeconds = 5;

    protected $listeners = [
        'echo:measurements,MeasurementCreated' => 'refreshFromEvent',
        'echo:alerts,AlertCreated' => 'refreshFromEvent',
    ];

    public function refreshFromEvent(): void
    {
        $this->dispatch('$refresh');
    }

    protected function getViewData(): array
    {
        $now = now();
        $sensorFreshCutoff = $now->copy()->subMinutes(10);
        $maintenanceCutoff = $now->copy()->subMonths(6);
        $transmissionWindow = $now->copy()->subHour();

        $latestMeasurementAt = Measurement::max('timestamp');
        $latestMeasurementAt = $latestMeasurementAt ? Carbon::parse($latestMeasurementAt) : null;

        $activeSensors = Sensor::whereHas('measurements', function ($query) use ($sensorFreshCutoff): void {
            $query->where('timestamp', '>=', $sensorFreshCutoff);
        })->count();

        $silentSensors = Sensor::whereDoesntHave('measurements', function ($query) use ($sensorFreshCutoff): void {
            $query->where('timestamp', '>=', $sensorFreshCutoff);
        })->count();

        $totalTransmissions = DataTransmission::where('timestamp', '>=', $transmissionWindow)->count();
        $successfulTransmissions = DataTransmission::where('timestamp', '>=', $transmissionWindow)
            ->where('successful', true)
            ->count();
        $transmissionRate = $totalTransmissions > 0
            ? round(($successfulTransmissions / $totalTransmissions) * 100)
            : null;

        $dueMaintenance = Sensor::whereNull('last_maintenance')
            ->orWhere('last_maintenance', '<=', $maintenanceCutoff)
            ->count();

        return [
            'stats' => [
                'locations' => Location::count(),
                'bins' => Bin::count(),
                'sensors' => Sensor::count(),
                'measurements' => Measurement::count(),
                'alerts_open' => Alert::where('is_resolved', false)->count(),
                'active_sensors' => $activeSensors,
                'silent_sensors' => $silentSensors,
                'maintenance_due' => $dueMaintenance,
            ],
            'latestMeasurementAt' => $latestMeasurementAt,
            'transmissionRate' => $transmissionRate,
            'recentMeasurements' => Measurement::with('sensor.bin.location')
                ->orderByDesc('timestamp')
                ->limit(8)
                ->get(),
            'recentAlerts' => Alert::with('bin.location')
                ->orderByDesc('timestamp')
                ->limit(6)
                ->get(),
            'locations' => Location::withCount('bins')
                ->orderBy('name')
                ->limit(6)
                ->get(),
            'upcomingCollections' => CollectionSchedule::with('location')
                ->orderBy('planned_time')
                ->limit(6)
                ->get(),
            'maintenanceLogs' => MaintenanceLog::with('sensor.bin.location')
                ->orderByDesc('maintenance_date')
                ->limit(6)
                ->get(),
            'analysisResults' => AnalysisResult::with('bin.location')
                ->orderByDesc('timestamp')
                ->limit(6)
                ->get(),
        ];
    }
}
