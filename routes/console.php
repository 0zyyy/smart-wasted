<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('perf:explain-hot-queries', function () {
    $driver = DB::connection()->getDriverName();
    $explainPrefix = $driver === 'sqlite' ? 'EXPLAIN QUERY PLAN ' : 'EXPLAIN ';

    $examples = [
        [
            'label' => 'Dedup measurement lookup',
            'sql' => 'SELECT * FROM measurements WHERE sensor_id = ? AND unit = ? AND value = ? AND timestamp >= ? ORDER BY timestamp DESC LIMIT 1',
            'bindings' => [1, '%', 75.0, now()->subSeconds(15)->toDateTimeString()],
        ],
        [
            'label' => 'Open/ack queue ordered by severity and time',
            'sql' => "SELECT * FROM alerts WHERE status IN ('open', 'acknowledged') ORDER BY CASE severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END, timestamp ASC LIMIT 10",
            'bindings' => [],
        ],
        [
            'label' => 'Open overflow existence check',
            'sql' => "SELECT * FROM alerts WHERE bin_id = ? AND type = 'Overflow' AND is_resolved = 0 LIMIT 1",
            'bindings' => [1],
        ],
        [
            'label' => 'Recent transmission success rate window',
            'sql' => 'SELECT COUNT(*) AS total, SUM(CASE WHEN successful = 1 THEN 1 ELSE 0 END) AS success_count FROM data_transmissions WHERE timestamp >= ?',
            'bindings' => [now()->subHour()->toDateTimeString()],
        ],
    ];

    foreach ($examples as $example) {
        $this->newLine();
        $this->info($example['label']);
        $this->line('SQL: ' . $example['sql']);

        $plan = DB::select($explainPrefix . $example['sql'], $example['bindings']);
        foreach ($plan as $row) {
            $this->line(json_encode((array) $row));
        }
    }
})->purpose('Explain execution plan for the hottest Smart Wasted queries');
