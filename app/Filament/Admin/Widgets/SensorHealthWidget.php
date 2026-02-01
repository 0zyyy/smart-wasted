<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Sensor;
use Filament\Widgets\ChartWidget;

class SensorHealthWidget extends ChartWidget
{
    protected ?string $heading = 'Sensor Health';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    public function getPollingInterval(): ?string
    {
        return '15s';
    }

    protected function getData(): array
    {
        $cutoff = now()->subMinutes(10);

        $activeSensors = Sensor::whereHas('measurements', function ($query) use ($cutoff) {
            $query->where('timestamp', '>=', $cutoff);
        })->count();

        $silentSensors = Sensor::whereDoesntHave('measurements', function ($query) use ($cutoff) {
            $query->where('timestamp', '>=', $cutoff);
        })->count();

        return [
            'datasets' => [
                [
                    'label' => 'Sensors',
                    'data' => [$activeSensors, $silentSensors],
                    'backgroundColor' => [
                        'rgb(16, 185, 129)',  // emerald-500 for active
                        'rgb(239, 68, 68)',   // red-500 for silent
                    ],
                ],
            ],
            'labels' => ['Active', 'Silent'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
