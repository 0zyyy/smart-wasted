<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\LiveTelemetryWidget;
use App\Filament\Admin\Widgets\LocationFillWidget;
use App\Filament\Admin\Widgets\OpenAlertsQueueWidget;
use App\Filament\Admin\Widgets\SensorHealthWidget;
use App\Filament\Admin\Widgets\BinFillTrendWidget;
use App\Filament\Admin\Widgets\FillLevelTrendWidget;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use App\Filament\Admin\Widgets\UpcomingCollectionsWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            LiveTelemetryWidget::class,
            OpenAlertsQueueWidget::class,
            LocationFillWidget::class,
            FillLevelTrendWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return ['default' => 1, 'lg' => 2];
    }
}
