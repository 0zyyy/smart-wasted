<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Location;
use App\Models\Measurement;
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
        $locations = Location::withCount('bins')->get();
        
        $labels = [];
        $fillPercentages = [];
        $colors = [];
        
        foreach ($locations as $location) {
            $labels[] = $location->name;
            
            // Get the latest fill level measurement for bins at this location
            $avgFill = Measurement::whereHas('sensor.bin', function ($query) use ($location) {
                $query->where('location_id', $location->location_id);
            })
            ->where('unit', 'LIKE', '%')
            ->latest('timestamp')
            ->limit(50)
            ->avg('value');
            
            $fillPercentages[] = round($avgFill ?? 0, 1);
            
            // Color based on fill level
            if ($avgFill >= 80) {
                $colors[] = 'rgb(239, 68, 68)';    // Red - needs attention
            } elseif ($avgFill >= 60) {
                $colors[] = 'rgb(245, 158, 11)';   // Amber - moderate
            } else {
                $colors[] = 'rgb(16, 185, 129)';   // Green - good
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Fill %',
                    'data' => $fillPercentages,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
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
