<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Measurement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class FillLevelTrendWidget extends ChartWidget
{
    protected static ?int $sort = 7;

    protected static ?string $heading = 'Fill Level Trend (Last 24h)';

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $hours = collect(range(23, 0))->map(fn ($h) => now()->subHours($h)->startOfHour());

        $labels = $hours->map(fn (Carbon $h) => $h->format('H:i'))->toArray();

        // Get avg fill % per hour across all ultrasonic sensors
        $readings = Measurement::query()
            ->where('unit', '%')
            ->where('timestamp', '>=', now()->subHours(24))
            ->selectRaw('AVG(value) as avg_fill, strftime(\'%Y-%m-%d %H\', timestamp) as hour_key')
            ->groupByRaw('strftime(\'%Y-%m-%d %H\', timestamp)')
            ->orderBy('hour_key')
            ->pluck('avg_fill', 'hour_key');

        $data = $hours->map(function (Carbon $h) use ($readings) {
            $key = $h->format('Y-m-d H');
            return round($readings[$key] ?? 0, 1);
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Avg Fill Level (%)',
                    'data' => $data,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
