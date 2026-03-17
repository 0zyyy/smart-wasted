<?php

namespace App\Filament\Pages;

use App\Models\Alert;
use App\Models\AnalysisResult;
use App\Models\CollectionSchedule;
use App\Models\MaintenanceLog;
use App\Models\Measurement;
use App\Services\DashboardCacheService;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class SmartWasteDashboard extends Page
{
    protected static ?string $title = 'Smart Waste Operations';
    protected static ?string $navigationLabel = 'Smart Waste';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bolt';
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
        $stats = DashboardCacheService::getStats();
        $transmissionRate = DashboardCacheService::getTransmissionRate();

        $latestMeasurementAt = Measurement::max('timestamp');
        $latestMeasurementAt = $latestMeasurementAt ? Carbon::parse($latestMeasurementAt) : null;

        return [
            'stats' => $stats,
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
            'locations' => $stats['locations_list'] ?? collect(),
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
