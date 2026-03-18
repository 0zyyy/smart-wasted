<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Measurement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class FillLevelTrendWidget extends ChartWidget
{
    protected static ?int $sort = 7;

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = ['default' => 1];

    protected string $view = 'filament.widgets.fill-level-trend-widget';

    public ?string $locationFilter = null;

    private const BIN_TYPES = [
        'Organic'   => '16, 185, 129',
        'Anorganic' => '59, 130, 246',
        'B3'        => '245, 158, 11',
    ];

    public function getHeading(): string
    {
        return match ((int) ($this->filter ?? 24)) {
            6       => 'Fill Level Trend (Last 6h)',
            168     => 'Fill Level Trend (Last 7 days)',
            default => 'Fill Level Trend (Last 24h)',
        };
    }

    public function getLocationOptions(): array
    {
        $locations = \App\Models\Location::orderBy('name')->pluck('name', 'location_id')->toArray();

        return ['' => 'All Locations'] + $locations;
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

        $locationId = $this->locationFilter ? (int) $this->locationFilter : null;

        foreach (self::BIN_TYPES as $type => $rgb) {
            $readings = Measurement::query()
                ->where('unit', '%')
                ->where('timestamp', '>=', $start)
                ->whereHas('sensor', fn ($q) => $q
                    ->where('type', 'Ultrasonic')
                    ->whereHas('bin', fn ($q2) => $q2
                        ->where('type', $type)
                        ->when($locationId, fn ($q3) => $q3->where('location_id', $locationId))))
                ->selectRaw("AVG(value) as avg_fill, {$fmt} as bucket_key")
                ->groupByRaw($fmt)
                ->orderBy('bucket_key')
                ->pluck('avg_fill', 'bucket_key');

            $data = $buckets->map(
                fn (Carbon $b) => round($readings[$b->format($keyFmt)] ?? 0, 1)
            )->toArray();

            $datasets[] = [
                'label'           => $type,
                'data'            => $data,
                'borderColor'     => "rgb({$rgb})",
                'backgroundColor' => "rgba({$rgb}, 0.1)",
                'fill'            => false,
                'tension'         => 0.4,
                'pointRadius'     => 3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels'   => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'min'   => 0,
                    'max'   => 100,
                    'title' => [
                        'display' => true,
                        'text'    => 'Fill (%)',
                    ],
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
