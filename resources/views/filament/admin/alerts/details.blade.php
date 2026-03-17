<div class="space-y-6">
    <section class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Quick Actions</h3>
        <div class="mt-3 flex flex-wrap items-center gap-2">
            {{ $action->getModalAction('quickAcknowledge') }}
            {{ $action->getModalAction('quickAssignMe') }}
            {{ $action->getModalAction('quickResolve') }}
            {{ $action->getModalAction('quickReopen') }}
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Alert Context</h3>
        <dl class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Type</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $alert->type }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Severity</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($alert->severity) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($alert->status) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Location</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $alert->bin?->location?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Assigned To</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $alert->assignedTo?->name ?? 'Unassigned' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Triggered</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ optional($alert->timestamp)?->format('Y-m-d H:i:s') ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500 dark:text-gray-400">Description</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $alert->description }}</dd>
            </div>
            @if($alert->resolution_note)
                <div class="sm:col-span-2">
                    <dt class="text-gray-500 dark:text-gray-400">Resolution Note</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $alert->resolution_note }}</dd>
                </div>
            @endif
        </dl>
    </section>

    <section class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent Measurements (Bin Scope)</h3>
        <div class="mt-3 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 dark:text-gray-400">
                        <th class="pb-2 pr-4 font-medium">When</th>
                        <th class="pb-2 pr-4 font-medium">Sensor</th>
                        <th class="pb-2 pr-4 font-medium">Type</th>
                        <th class="pb-2 pr-4 font-medium">Value</th>
                    </tr>
                </thead>
                <tbody class="text-gray-900 dark:text-gray-100">
                    @forelse($recentMeasurements as $measurement)
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="py-2 pr-4">{{ optional($measurement->timestamp)?->format('Y-m-d H:i:s') }}</td>
                            <td class="py-2 pr-4">#{{ $measurement->sensor_id }}</td>
                            <td class="py-2 pr-4">{{ $measurement->sensor?->type ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ number_format((float) $measurement->value, 2) }} {{ $measurement->unit }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-3 text-gray-500 dark:text-gray-400">No measurements found for this bin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Activity Timeline</h3>
        <ul class="mt-3 space-y-3">
            @forelse($activities as $activity)
                <li class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($activity->action) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($activity->created_at)?->format('Y-m-d H:i:s') }}</p>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Actor: {{ $activity->actor?->name ?? 'System' }}</p>
                    @if($activity->note)
                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $activity->note }}</p>
                    @endif
                </li>
            @empty
                <li class="text-sm text-gray-500 dark:text-gray-400">No activity has been logged yet.</li>
            @endforelse
        </ul>
    </section>
</div>
