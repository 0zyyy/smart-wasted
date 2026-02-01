<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Alert;
use App\Models\Bin;
use App\Models\DataTransmission;
use App\Models\Location;
use App\Models\Measurement;
use App\Models\Sensor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $now = now();
        $sensorFreshCutoff = $now->copy()->subMinutes(10);
        $transmissionWindow = $now->copy()->subHour();
        $maintenanceCutoff = $now->copy()->subMonths(6);

        $openAlerts = Alert::where('is_resolved', false)->count();
        
        $activeSensors = Sensor::whereHas('measurements', function ($query) use ($sensorFreshCutoff) {
            $query->where('timestamp', '>=', $sensorFreshCutoff);
        })->count();

        $silentSensors = Sensor::whereDoesntHave('measurements', function ($query) use ($sensorFreshCutoff) {
            $query->where('timestamp', '>=', $sensorFreshCutoff);
        })->count();

        $totalTransmissions = DataTransmission::where('timestamp', '>=', $transmissionWindow)->count();
        $successfulTransmissions = DataTransmission::where('timestamp', '>=', $transmissionWindow)
            ->where('successful', true)
            ->count();
        $transmissionRate = $totalTransmissions > 0
            ? round(($successfulTransmissions / $totalTransmissions) * 100) . '%'
            : 'No data';

        $dueMaintenance = Sensor::whereNull('last_maintenance')
            ->orWhere('last_maintenance', '<=', $maintenanceCutoff)
            ->count();

        return [
            Stat::make('Locations', Location::count())
                ->description('Operational hubs')
                ->icon('heroicon-o-map-pin')
                ->color('primary'),
            Stat::make('Bins', Bin::count())
                ->description('Tracked containers')
                ->icon('heroicon-o-trash')
                ->color('success'),
            Stat::make('Sensors', Sensor::count())
                ->description($activeSensors . ' active, ' . $silentSensors . ' silent')
                ->icon('heroicon-o-cpu-chip')
                ->color('info'),
            Stat::make('Measurements', Measurement::count())
                ->description('Total readings')
                ->icon('heroicon-o-chart-bar')
                ->color('gray'),
        ];
    }
}
