<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\LiveTelemetryWidget;
use App\Filament\Admin\Widgets\LocationFillWidget;
use App\Filament\Admin\Widgets\SensorHealthWidget;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    // Define which widgets to show and in what order
    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            LiveTelemetryWidget::class,
            SensorHealthWidget::class,
            LocationFillWidget::class,
        ];
    }

    // Optional: Customize the number of columns
    public function getColumns(): array|int
    {
        return 2;
    }
}
