<div class="sw-dashboard space-y-8" wire:poll.{{ $refreshSeconds }}s>
    <section class="relative overflow-hidden rounded-3xl border border-emerald-200/60 bg-gradient-to-br from-amber-50 via-emerald-50 to-sky-100 p-8 shadow-lg">
        <div class="absolute -top-24 -right-16 h-56 w-56 rounded-full bg-emerald-200/60 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-10 h-48 w-48 rounded-full bg-sky-200/60 blur-3xl"></div>

        <div class="relative z-10 grid gap-8 lg:grid-cols-[1.3fr_0.7fr]">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/70 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 shadow-sm">
                    Realtime Ops
                    <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.65)]"></span>
                </div>
                <div class="space-y-3">
                    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 md:text-5xl">
                        Smart Waste Command Center
                    </h1>
                    <p class="max-w-2xl text-sm font-medium text-slate-600 md:text-base">
                        Monitor bin health, sensor integrity, and collection readiness in near-realtime. This view
                        refreshes every {{ $refreshSeconds }} seconds to keep your crews in sync.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white">
                        Pulse
                        <span class="text-emerald-300">
                            {{ $latestMeasurementAt ? $latestMeasurementAt->diffForHumans() : 'Waiting for data' }}
                        </span>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-700">
                        Transmission Rate
                        <span class="text-emerald-700">
                            {{ $transmissionRate !== null ? $transmissionRate . '%' : 'No data' }}
                        </span>
                    </span>
                </div>
            </div>
            <div class="grid gap-4">
                <div class="rounded-2xl border border-white/70 bg-white/80 p-5 shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Active Sensors</p>
                    <div class="mt-3 flex items-end justify-between">
                        <span class="text-4xl font-semibold text-slate-900">{{ $stats['active_sensors'] }}</span>
                        <span class="text-xs font-semibold text-emerald-600">last 10 min</span>
                    </div>
                    <div class="mt-4 h-2 w-full rounded-full bg-emerald-100">
                        @php
                            $sensorTotal = max($stats['sensors'], 1);
                            $activePercent = round(($stats['active_sensors'] / $sensorTotal) * 100);
                        @endphp
                        <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $activePercent }}%"></div>
                    </div>
                </div>
                <div class="rounded-2xl border border-white/70 bg-white/80 p-5 shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Silent Sensors</p>
                    <div class="mt-3 flex items-end justify-between">
                        <span class="text-4xl font-semibold text-slate-900">{{ $stats['silent_sensors'] }}</span>
                        <span class="text-xs font-semibold text-rose-500">needs attention</span>
                    </div>
                    <div class="mt-4 h-2 w-full rounded-full bg-rose-100">
                        @php
                            $silentPercent = round(($stats['silent_sensors'] / $sensorTotal) * 100);
                        @endphp
                        <div class="h-2 rounded-full bg-rose-400" style="width: {{ $silentPercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-4">
        <div class="sw-card">
            <p class="sw-card__label">Locations</p>
            <div class="sw-card__value">{{ $stats['locations'] }}</div>
            <p class="sw-card__meta">Operational hubs</p>
        </div>
        <div class="sw-card">
            <p class="sw-card__label">Bins</p>
            <div class="sw-card__value">{{ $stats['bins'] }}</div>
            <p class="sw-card__meta">Tracked containers</p>
        </div>
        <div class="sw-card">
            <p class="sw-card__label">Sensors</p>
            <div class="sw-card__value">{{ $stats['sensors'] }}</div>
            <p class="sw-card__meta">IoT endpoints</p>
        </div>
        <div class="sw-card">
            <p class="sw-card__label">Open Alerts</p>
            <div class="sw-card__value text-rose-600">{{ $stats['alerts_open'] }}</div>
            <p class="sw-card__meta">{{ $stats['maintenance_due'] }} maintenance due</p>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="sw-panel">
            <div class="sw-panel__header">
                <div>
                    <p class="sw-panel__title">Live Telemetry</p>
                    <p class="sw-panel__subtitle">Latest measurements streaming in from sensors.</p>
                </div>
                <span class="sw-pill">Auto refresh</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentMeasurements as $measurement)
                    <div class="flex flex-wrap items-center justify-between gap-4 py-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $measurement->sensor?->bin?->location?->name ?? 'Unknown location' }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Sensor {{ $measurement->sensor?->sensor_id ?? 'N/A' }}
                                · Bin {{ $measurement->sensor?->bin?->bin_id ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-lg font-semibold text-slate-900">
                                {{ number_format($measurement->value, 2) }} {{ $measurement->unit }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">
                                {{ $measurement->timestamp?->diffForHumans() ?? 'just now' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-sm text-slate-500">
                        No measurements yet. Push data into the API to populate this feed.
                    </div>
                @endforelse
            </div>
        </div>
        <div class="sw-panel">
            <div class="sw-panel__header">
                <div>
                    <p class="sw-panel__title">Active Alerts</p>
                    <p class="sw-panel__subtitle">Escalations and anomalies requiring response.</p>
                </div>
                <span class="sw-pill sw-pill--alert">{{ $stats['alerts_open'] }} open</span>
            </div>
            <div class="space-y-3">
                @forelse ($recentAlerts as $alert)
                    <div class="rounded-2xl border border-rose-100 bg-rose-50/50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-rose-700">{{ $alert->type }}</p>
                            <span class="text-xs text-rose-500">{{ $alert->timestamp?->diffForHumans() }}</span>
                        </div>
                        <p class="mt-2 text-xs text-rose-600">
                            {{ $alert->description }}
                        </p>
                        <p class="mt-2 text-xs text-slate-500">
                            Bin {{ $alert->bin?->bin_id ?? 'N/A' }} · {{ $alert->bin?->location?->name ?? 'Unknown' }}
                        </p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4 text-center text-sm text-emerald-700">
                        All clear. No alerts right now.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="sw-panel">
        <div class="sw-panel__header">
            <div>
                <p class="sw-panel__title">Location Pulse</p>
                <p class="sw-panel__subtitle">Monitor coverage and bin density across the grid.</p>
            </div>
            <span class="sw-pill">Map view ready</span>
        </div>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($locations as $location)
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">{{ $location->name }}</p>
                    <p class="text-xs text-slate-500">
                        {{ $location->address ?? 'Address not set' }}
                    </p>
                    <div class="mt-4 flex items-center justify-between text-xs">
                        <span class="text-slate-500">{{ $location->bins_count }} bins</span>
                        <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] uppercase tracking-[0.2em] text-slate-500">
                            {{ $location->latitude !== null ? 'Geo mapped' : 'No GPS' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                    Add locations to light up the grid.
                </div>
            @endforelse
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="sw-panel">
            <div class="sw-panel__header">
                <div>
                    <p class="sw-panel__title">Collection Schedule</p>
                    <p class="sw-panel__subtitle">Next pickups per location.</p>
                </div>
            </div>
            <div class="space-y-4">
                @forelse ($upcomingCollections as $schedule)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                        <p class="text-sm font-semibold text-slate-900">{{ $schedule->location?->name ?? 'Unknown' }}</p>
                        <p class="text-xs text-slate-500">{{ $schedule->planned_time?->format('M d, Y H:i') }}</p>
                        <p class="mt-2 text-xs text-slate-600">Crew: {{ $schedule->collector_name }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                        Schedule collections to populate this panel.
                    </div>
                @endforelse
            </div>
        </div>
        <div class="sw-panel">
            <div class="sw-panel__header">
                <div>
                    <p class="sw-panel__title">Maintenance Log</p>
                    <p class="sw-panel__subtitle">Recent sensor servicing events.</p>
                </div>
                <span class="sw-pill sw-pill--warn">{{ $stats['maintenance_due'] }} due</span>
            </div>
            <div class="space-y-4">
                @forelse ($maintenanceLogs as $log)
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-4">
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $log->sensor?->bin?->location?->name ?? 'Unknown' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ $log->maintenance_date?->format('M d, Y') }} · {{ $log->technician_name }}
                        </p>
                        <p class="mt-2 text-xs text-amber-700">{{ $log->action_taken }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                        Maintenance events will appear here.
                    </div>
                @endforelse
            </div>
        </div>
        <div class="sw-panel">
            <div class="sw-panel__header">
                <div>
                    <p class="sw-panel__title">Analysis Results</p>
                    <p class="sw-panel__subtitle">Insights and anomaly detection output.</p>
                </div>
            </div>
            <div class="space-y-4">
                @forelse ($analysisResults as $result)
                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <p class="text-sm font-semibold text-slate-900">{{ $result->analysis_type }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $result->bin?->location?->name ?? 'Unknown' }} · {{ $result->timestamp?->diffForHumans() }}
                        </p>
                        <p class="mt-2 text-xs text-slate-600">
                            {{ is_array($result->result_data) ? json_encode($result->result_data) : $result->result_data }}
                        </p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                        Run analytics to surface insights here.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
