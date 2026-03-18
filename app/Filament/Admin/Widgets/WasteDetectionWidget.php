<?php

namespace App\Filament\Admin\Widgets;

use App\Models\WasteDetection;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class WasteDetectionWidget extends ChartWidget
{
    protected static ?int $sort = 6;

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    private const CLASSES = [
        'Organic'   => '16, 185, 129',
        'Anorganic' => '59, 130, 246',
        'B3'        => '245, 158, 11',
    ];

    public function getHeading(): string
    {
        return match ((int) ($this->filter ?? 24)) {
            6       => 'Waste Detections (Last 6h)',
            168     => 'Waste Detections (Last 7 days)',
            default => 'Waste Detections (Last 24h)',
        };
    }

    protected function getFilters(): ?array
    {
        return [
            '6'   => 'Last 6 hours',
            '24'  => 'Last 24 hours',
            '168' => 'Last 7 days',
        ];
    }

    protected function getData(): array
    {
        $hours    = (int) ($this->filter ?? 24);
        $isWeekly = $hours === 168;

        if ($isWeekly) {
            $buckets = collect(range(6, 0))->map(fn ($d) => now()->subDays($d)->startOfDay());
            $labels  = $buckets->map(fn (Carbon $d) => $d->format('M j'))->toArray();
            $fmt     = $this->dayFormat();
            $keyFmt  = 'Y-m-d';
            $start   = now()->subDays(7);
        } else {
            $buckets = collect(range($hours - 1, 0))->map(fn ($h) => now()->subHours($h)->startOfHour());
            $labels  = $buckets->map(fn (Carbon $h) => $h->format('H:i'))->toArray();
            $fmt     = $this->hourFormat();
            $keyFmt  = 'Y-m-d H';
            $start   = now()->subHours($hours);
        }

        $datasets = [];

        foreach (self::CLASSES as $class => $rgb) {
            $counts = WasteDetection::query()
                ->where('detected_class', $class)
                ->where('timestamp', '>=', $start)
                ->selectRaw("COUNT(*) as total, {$fmt} as bucket_key")
                ->groupByRaw($fmt)
                ->orderBy('bucket_key')
                ->pluck('total', 'bucket_key');

            $data = $buckets->map(
                fn (Carbon $b) => (int) ($counts[$b->format($keyFmt)] ?? 0)
            )->toArray();

            $datasets[] = [
                'label'           => $class,
                'data'            => $data,
                'backgroundColor' => "rgba({$rgb}, 0.7)",
                'borderColor'     => "rgb({$rgb})",
                'borderWidth'     => 1,
            ];
        }

        return ['datasets' => $datasets, 'labels' => $labels];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'top'],
            ],
            'scales' => [
                'x' => ['stacked' => true],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'title' => ['display' => true, 'text' => 'Detections'],
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }

    private function hourFormat(): string
    {
        return \DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%d %H', timestamp)"
            : "DATE_FORMAT(timestamp, '%Y-%m-%d %H')";
    }

    private function dayFormat(): string
    {
        return \DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%d', timestamp)"
            : "DATE_FORMAT(timestamp, '%Y-%m-%d')";
    }
}
