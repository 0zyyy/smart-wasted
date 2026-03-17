<?php

namespace App\Filament\Admin\Widgets;

use App\Services\DashboardCacheService;
use Filament\Widgets\ChartWidget;

class LocationFillWidget extends ChartWidget
{
    protected ?string $heading = 'Location Fill Levels';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public function getPollingInterval(): ?string
    {
        return '15s';
    }

    protected function getData(): array
    {
        $data = DashboardCacheService::getLocationFill();

        return [
            'datasets' => [
                [
                    'label' => 'Fill %',
                    'data' => $data['fillPercentages'],
                    'backgroundColor' => $data['colors'],
                    'borderColor' => $data['colors'],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'min' => 0,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Fill Percentage (%)',
                    ],
                ],
            ],
        ];
    }
}

