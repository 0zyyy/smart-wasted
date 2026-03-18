<?php

namespace App\Filament\Admin\Widgets;

use App\Models\DataTransmission;
use App\Models\Measurement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PipelineHealthWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $window = now()->subHours(24);

        // Latency stats from measurements with latency_ms recorded
        $latency = Measurement::query()
            ->whereNotNull('latency_ms')
            ->where('timestamp', '>=', $window)
            ->selectRaw('AVG(latency_ms) as avg_ms, MIN(latency_ms) as min_ms, MAX(latency_ms) as max_ms, COUNT(*) as total')
            ->first();

        // P95 latency: skip bottom 95%, take the next value
        $p95 = null;
        if ($latency && $latency->total > 0) {
            $offset = max(0, (int) floor($latency->total * 0.95) - 1);
            $p95 = Measurement::query()
                ->whereNotNull('latency_ms')
                ->where('timestamp', '>=', $window)
                ->orderBy('latency_ms')
                ->skip($offset)
                ->value('latency_ms');
        }

        // Reliability from data_transmissions
        $totalTx      = DataTransmission::where('timestamp', '>=', $window)->count();
        $successfulTx = DataTransmission::where('timestamp', '>=', $window)->where('successful', true)->count();
        $reliability  = $totalTx > 0 ? round(($successfulTx / $totalTx) * 100, 1) : null;

        $avgMs  = $latency?->avg_ms  ? (int) round($latency->avg_ms)  : null;
        $minMs  = $latency?->min_ms  ? (int) $latency->min_ms          : null;
        $maxMs  = $latency?->max_ms  ? (int) $latency->max_ms          : null;

        return [
            Stat::make('Avg Latency', $avgMs !== null ? "{$avgMs} ms" : 'No data')
                ->description('Mean pipeline delay (24h)')
                ->icon('heroicon-o-clock')
                ->color($avgMs === null ? 'gray' : ($avgMs < 300 ? 'success' : ($avgMs < 800 ? 'warning' : 'danger'))),

            Stat::make('P95 Latency', $p95 !== null ? "{$p95} ms" : 'No data')
                ->description("Min {$minMs} ms / Max {$maxMs} ms")
                ->icon('heroicon-o-chart-bar')
                ->color($p95 === null ? 'gray' : ($p95 < 800 ? 'success' : ($p95 < 2000 ? 'warning' : 'danger'))),

            Stat::make('Reliability', $reliability !== null ? "{$reliability}%" : 'No data')
                ->description("{$successfulTx} / {$totalTx} packets received (24h)")
                ->icon('heroicon-o-signal')
                ->color($reliability === null ? 'gray' : ($reliability >= 99 ? 'success' : ($reliability >= 95 ? 'warning' : 'danger'))),

            Stat::make('Packets (24h)', $totalTx ?: 'No data')
                ->description($latency?->total ? "{$latency->total} measurements with latency data" : 'No latency data yet')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info'),
        ];
    }
}
