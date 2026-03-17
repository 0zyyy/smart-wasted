<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Bin;
use App\Models\Measurement;
use App\Models\Sensor;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class BinFillTrendWidget extends ChartWidget
{
    protected static ?int $sort = 8;

    protected ?string $heading = 'Per-Bin Fill Trend (Last 24h)';

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    #[On('measurement-created')]
    public function refresh(): void
    {
        $this->updateChartData();
    }

    protected function getFilters(): ?array
    {
        $bins = Bin::with('location')->get();

        return $bins->mapWithKeys(function (Bin $bin) {
            $label = ($bin->location?->name ?? '?') . ' - ' . $bin->type;
            return [$bin->bin_id => $label];
        })->toArray();
    }

    protected function getData(): array
    {
        $hours = collect(range(23, 0))->map(fn ($h) => now()->subHours($h)->startOfHour());
        $labels = $hours->map(fn (Carbon $h) => $h->format('H:i'))->toArray();

        $selectedBinId = $this->filter;

        if ($selectedBinId) {
            $ultrasonicSensorId = Sensor::where('bin_id', $selectedBinId)
                ->where('type', 'Ultrasonic')
                ->value('sensor_id');

            if (!$ultrasonicSensorId) {
                return $this->emptyDataset($labels);
            }

            $fmt      = $this->hourFormat();
            $readings = Measurement::query()
                ->where('sensor_id', $ultrasonicSensorId)
                ->where('unit', '%')
                ->where('timestamp', '>=', now()->subHours(24))
                ->selectRaw("AVG(value) as avg_fill, {$fmt} as hour_key")
                ->groupByRaw($fmt)
                ->orderBy('hour_key')
                ->pluck('avg_fill', 'hour_key');

            $bin = Bin::with('location')->find($selectedBinId);
            $datasetLabel = ($bin?->location?->name ?? '?') . ' - ' . ($bin?->type ?? '?') . ' Fill (%)';
        } else {
            $fmt      = $this->hourFormat();
            $readings = Measurement::query()
                ->where('unit', '%')
                ->where('timestamp', '>=', now()->subHours(24))
                ->selectRaw("AVG(value) as avg_fill, {$fmt} as hour_key")
                ->groupByRaw($fmt)
                ->orderBy('hour_key')
                ->pluck('avg_fill', 'hour_key');

            $datasetLabel = 'Avg Fill Level (%)';
        }

        $data = $hours->map(function (Carbon $h) use ($readings) {
            $key = $h->format('Y-m-d H');
            return round($readings[$key] ?? 0, 1);
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => $datasetLabel,
                    'data' => $data,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
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

    private function emptyDataset(array $labels): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Fill (%)',
                    'data' => array_fill(0, count($labels), 0),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function hourFormat(): string
    {
        return \DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%d %H', timestamp)"
            : "DATE_FORMAT(timestamp, '%Y-%m-%d %H')";
    }
}
