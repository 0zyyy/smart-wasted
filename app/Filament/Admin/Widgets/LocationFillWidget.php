<?php

namespace App\Filament\Admin\Widgets;

use App\Services\DashboardCacheService;
use Filament\Widgets\ChartWidget;

class LocationFillWidget extends ChartWidget
{
    protected ?string $heading = 'Fill Levels by Location';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

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
        $location = \App\Models\Location::find($locationId);
        $binTypes = ['Organic', 'Anorganic', 'B3'];
        $colors   = [
            'Organic'   => 'rgba(16, 185, 129, 0.8)',
            'Anorganic' => 'rgba(59, 130, 246, 0.8)',
            'B3'        => 'rgba(245, 158, 11, 0.8)',
        ];

        $labels = [];
        $datasets = [];

        foreach ($binTypes as $type) {
            $fill = \App\Models\Measurement::whereHas('sensor', function ($q) use ($locationId, $type) {
                $q->where('type', 'Ultrasonic')
                  ->whereHas('bin', fn ($q2) => $q2
                      ->where('location_id', $locationId)
                      ->where('type', $type));
            })
                ->where('unit', '%')
                ->latest('timestamp')
                ->value('value');

            $labels[]   = $type;
            $datasets[] = [
                'label'           => $type,
                'data'            => [round($fill ?? 0, 1)],
                'backgroundColor' => $colors[$type],
                'borderColor'     => $colors[$type],
                'borderWidth'     => 1,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels'   => [$location?->name ?? 'Unknown'],
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

