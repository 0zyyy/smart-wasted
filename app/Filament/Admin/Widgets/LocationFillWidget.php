<?php

namespace App\Filament\Admin\Widgets;

use App\Services\DashboardCacheService;
use Filament\Widgets\ChartWidget;

class LocationFillWidget extends ChartWidget
{
    protected ?string $heading = 'Fill Levels by Location';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['default' => 1];

    public function getPollingInterval(): ?string
    {
        return '15s';
    }

    protected function getFilters(): ?array
    {
        $locations = \App\Models\Location::orderBy('name')->pluck('name', 'location_id')->toArray();

        return ['' => 'All Locations'] + $locations;
    }

    protected function getData(): array
    {
        $locationId = $this->filter ?: null;

        if ($locationId) {
            return $this->getFilteredData((int) $locationId);
        }

        $data = DashboardCacheService::getLocationFill();

        return [
            'datasets' => $data['datasets'],
            'labels'   => $data['labels'],
        ];
    }

    private function getFilteredData(int $locationId): array
    {
        $colors = [
            'Organic'   => 'rgba(16, 185, 129, 0.8)',
            'Anorganic' => 'rgba(59, 130, 246, 0.8)',
            'B3'        => 'rgba(245, 158, 11, 0.8)',
        ];

        // Single query: latest fill % per bin type using subquery join
        $latestPerType = \DB::table('measurements as m2')
            ->join('sensors as s2', 's2.sensor_id', '=', 'm2.sensor_id')
            ->join('bins as b2', 'b2.bin_id', '=', 's2.bin_id')
            ->where('m2.unit', '%')
            ->where('s2.type', 'Ultrasonic')
            ->where('b2.location_id', $locationId)
            ->selectRaw('b2.type as bin_type, MAX(m2.timestamp) as max_ts')
            ->groupBy('b2.type');

        $fills = \DB::table('measurements as m')
            ->joinSub($latestPerType, 'latest', fn ($j) => $j->on('m.timestamp', '=', 'latest.max_ts'))
            ->join('sensors as s', 's.sensor_id', '=', 'm.sensor_id')
            ->join('bins as b', 'b.bin_id', '=', 's.bin_id')
            ->whereColumn('b.type', 'latest.bin_type')
            ->where('b.location_id', $locationId)
            ->where('m.unit', '%')
            ->groupBy('b.type')
            ->selectRaw('b.type as bin_type, AVG(m.value) as fill_value')
            ->pluck('fill_value', 'bin_type');

        $locationName = \App\Models\Location::where('location_id', $locationId)->value('name') ?? 'Unknown';

        $datasets = [];
        foreach (array_keys($colors) as $type) {
            $datasets[] = [
                'label'           => $type,
                'data'            => [round($fills[$type] ?? 0, 1)],
                'backgroundColor' => $colors[$type],
                'borderColor'     => $colors[$type],
                'borderWidth'     => 1,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels'   => [$locationName],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => false,
                ],
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
}

