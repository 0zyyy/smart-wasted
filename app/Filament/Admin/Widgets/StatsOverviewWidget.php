<?php

namespace App\Filament\Admin\Widgets;

use App\Services\DashboardCacheService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '10s';

    #[\Livewire\Attributes\On('measurement-created')]
    #[\Livewire\Attributes\On('alert-created')]
    public function refresh(): void {}

    protected function getStats(): array
    {
        $stats = DashboardCacheService::getStats();
        $transmissionRate = DashboardCacheService::getTransmissionRate();

        return [
            Stat::make('Locations', $stats['locations'])
                ->description('Operational hubs')
                ->icon('heroicon-o-map-pin')
                ->color('primary'),
            Stat::make('Bins', $stats['bins'])
                ->description('Tracked containers')
                ->icon('heroicon-o-trash')
                ->color('success'),
            Stat::make('Sensors', $stats['sensors'])
                ->description($stats['active_sensors'] . ' active, ' . $stats['silent_sensors'] . ' silent')
                ->icon('heroicon-o-cpu-chip')
                ->color('info'),
            Stat::make('Open Alerts', $stats['alerts_open'])
                ->description(($transmissionRate !== null ? "TX rate {$transmissionRate}%" : 'No TX data') . ", {$stats['maintenance_due']} maint. due")
                ->icon('heroicon-o-exclamation-triangle')
                ->color($stats['alerts_open'] > 0 ? 'danger' : 'warning'),
        ];
    }
}

