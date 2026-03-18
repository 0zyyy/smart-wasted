@php use Illuminate\Support\Carbon; @endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap');

    .sw-ops {
        --bg:           #06090f;
        --surface:      #0d1117;
        --panel:        #161b22;
        --border:       #21262d;
        --border-hi:    #30363d;
        --text:         #e6edf3;
        --text-muted:   #8b949e;
        --text-dim:     #484f58;
        --green:        #3fb950;
        --amber:        #d29922;
        --red:          #f85149;
        --blue:         #58a6ff;
        --cyan:         #79c0ff;
        --glow-green:   rgba(63, 185, 80, 0.12);
        --glow-red:     rgba(248, 81, 73, 0.12);
        --glow-amber:   rgba(210, 153, 34, 0.12);

        font-family: 'Sora', system-ui, sans-serif;
        background: var(--bg);
        color: var(--text);
        padding: 0 0 5rem;
        min-height: 100vh;
    }

    /* ---- Animations ---- */
    @keyframes pulse-ring {
        0%,100% { opacity:1; box-shadow: 0 0 0 0 currentColor; }
        50%      { opacity:.7; box-shadow: 0 0 0 5px transparent; }
    }
    @keyframes fade-up {
        from { opacity:0; transform: translateY(14px); }
        to   { opacity:1; transform: translateY(0); }
    }
    @keyframes scan-line {
        0%   { transform: translateY(-100%); }
        100% { transform: translateY(100vh); }
    }

    .sw-ops * { box-sizing: border-box; }

    /* ===== HERO ===== */
    .sw-hero {
        position: relative;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 1.25rem;
        padding: 2.5rem 2.75rem;
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .sw-hero__grid-bg {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(var(--border) 1px, transparent 1px),
            linear-gradient(90deg, var(--border) 1px, transparent 1px);
        background-size: 44px 44px;
        opacity: 0.45;
        pointer-events: none;
    }
    .sw-hero__glow {
        position: absolute;
        top: -60px; right: -40px;
        width: 380px; height: 380px;
        background: radial-gradient(circle, rgba(63,185,80,.07) 0%, transparent 70%);
        pointer-events: none;
    }
    .sw-hero__glow-2 {
        position: absolute;
        bottom: -80px; left: -60px;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(88,166,255,.05) 0%, transparent 70%);
        pointer-events: none;
    }
    .sw-hero__body {
        position: relative;
        z-index: 1;
        display: grid;
        gap: 2.5rem;
        grid-template-columns: 1.35fr 0.65fr;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .sw-hero__body { grid-template-columns: 1fr; }
    }
    .sw-hero__left { display: flex; flex-direction: column; gap: 1.25rem; }

    /* Status chips */
    .sw-chip {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-family: 'JetBrains Mono', monospace;
        font-size: .68rem;
        font-weight: 500;
        letter-spacing: .12em;
        text-transform: uppercase;
        padding: .3rem .8rem;
        border-radius: .375rem;
        border: 1px solid;
    }
    .sw-chip--green {
        background: var(--glow-green);
        border-color: rgba(63,185,80,.3);
        color: var(--green);
    }
    .sw-chip--amber {
        background: var(--glow-amber);
        border-color: rgba(210,153,34,.3);
        color: var(--amber);
    }
    .sw-chip--red {
        background: var(--glow-red);
        border-color: rgba(248,81,73,.3);
        color: var(--red);
    }
    .sw-chip--blue {
        background: rgba(88,166,255,.08);
        border-color: rgba(88,166,255,.25);
        color: var(--blue);
    }
    .sw-chip .dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: currentColor;
        animation: pulse-ring 2s ease-in-out infinite;
        flex-shrink: 0;
    }
    .sw-chip--red .dot { animation-duration: 1s; }

    .sw-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .sw-hero__title {
        font-size: clamp(1.8rem, 2.8vw, 2.75rem);
        font-weight: 800;
        letter-spacing: -.04em;
        color: var(--text);
        line-height: 1.08;
        margin: 0;
    }
    .sw-hero__title em {
        font-style: normal;
        color: var(--green);
    }
    .sw-hero__sub {
        font-size: .875rem;
        color: var(--text-muted);
        line-height: 1.75;
        margin: 0;
        max-width: 520px;
    }
    .sw-hero__meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
    }
    .sw-hero__meta-item {
        font-family: 'JetBrains Mono', monospace;
        font-size: .7rem;
        color: var(--text-dim);
    }
    .sw-hero__meta-item span {
        color: var(--text-muted);
    }

    /* Mini gauges (right side of hero) */
    .sw-hero__right {
        display: flex;
        flex-direction: column;
        gap: .875rem;
    }
    .sw-gauge {
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: .875rem;
        padding: 1.125rem 1.25rem;
        transition: border-color .2s;
    }
    .sw-gauge:hover { border-color: var(--border-hi); }
    .sw-gauge__label {
        font-family: 'JetBrains Mono', monospace;
        font-size: .63rem;
        text-transform: uppercase;
        letter-spacing: .15em;
        color: var(--text-dim);
        margin-bottom: .625rem;
    }
    .sw-gauge__row {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: .5rem;
    }
    .sw-gauge__val {
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--text);
        line-height: 1;
    }
    .sw-gauge__tag {
        font-family: 'JetBrains Mono', monospace;
        font-size: .63rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
    }
    .sw-gauge__tag--green { color: var(--green); }
    .sw-gauge__tag--red   { color: var(--red); }
    .sw-bar { height: 3px; background: var(--border); border-radius: 2px; overflow: hidden; }
    .sw-bar__fill {
        height: 100%;
        border-radius: 2px;
        transition: width 1.2s ease;
    }
    .sw-bar__fill--green { background: var(--green); }
    .sw-bar__fill--red   { background: var(--red); }

    /* ===== SECTION DIVIDER ===== */
    .sw-divider {
        display: flex;
        align-items: center;
        gap: .625rem;
        margin: 1.5rem 0 1rem;
    }
    .sw-divider__label {
        font-family: 'JetBrains Mono', monospace;
        font-size: .63rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .2em;
        color: var(--text-dim);
        white-space: nowrap;
    }
    .sw-divider__label::before { content: '// '; color: var(--green); opacity: .8; }
    .sw-divider__line { flex: 1; height: 1px; background: var(--border); }

    /* ===== STAT CARDS ===== */
    .sw-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: .875rem;
    }
    @media (max-width: 1024px) { .sw-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 540px)  { .sw-stats { grid-template-columns: 1fr; } }

    .sw-stat {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 1rem;
        padding: 1.25rem 1.375rem;
        position: relative;
        overflow: hidden;
        transition: border-color .2s, transform .15s;
        animation: fade-up .4s ease both;
    }
    .sw-stat:hover { border-color: var(--border-hi); transform: translateY(-2px); }
    .sw-stat::before {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 2px;
    }
    .sw-stat--green::before  { background: var(--green); }
    .sw-stat--blue::before   { background: var(--blue); }
    .sw-stat--cyan::before   { background: var(--cyan); }
    .sw-stat--red::before    { background: var(--red); }
    .sw-stat--amber::before  { background: var(--amber); }
    .sw-stat:nth-child(1) { animation-delay: .05s; }
    .sw-stat:nth-child(2) { animation-delay: .1s; }
    .sw-stat:nth-child(3) { animation-delay: .15s; }
    .sw-stat:nth-child(4) { animation-delay: .2s; }

    .sw-stat__val {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--text);
        line-height: 1;
        margin: 0 0 .375rem;
    }
    .sw-stat--red .sw-stat__val  { color: var(--red); }
    .sw-stat--amber .sw-stat__val { color: var(--amber); }
    .sw-stat__name {
        font-size: .8125rem;
        font-weight: 600;
        color: var(--text);
        margin: 0 0 .2rem;
    }
    .sw-stat__meta {
        font-family: 'JetBrains Mono', monospace;
        font-size: .63rem;
        color: var(--text-dim);
        margin: 0;
    }

    /* ===== PANEL ===== */
    .sw-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 1rem;
        padding: 1.375rem 1.5rem;
        animation: fade-up .4s ease both;
        animation-delay: .25s;
    }
    .sw-panel__hd {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }
    .sw-panel__title {
        font-size: .875rem;
        font-weight: 600;
        color: var(--text);
        margin: 0 0 .2rem;
    }
    .sw-panel__sub {
        font-family: 'JetBrains Mono', monospace;
        font-size: .6rem;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: .1em;
        margin: 0;
    }
    .sw-badge {
        font-family: 'JetBrains Mono', monospace;
        font-size: .63rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .08em;
        padding: .25rem .625rem;
        border-radius: .375rem;
        border: 1px solid;
        white-space: nowrap;
    }
    .sw-badge--green {
        background: var(--glow-green);
        border-color: rgba(63,185,80,.25);
        color: var(--green);
    }
    .sw-badge--red {
        background: var(--glow-red);
        border-color: rgba(248,81,73,.25);
        color: var(--red);
    }
    .sw-badge--amber {
        background: var(--glow-amber);
        border-color: rgba(210,153,34,.25);
        color: var(--amber);
    }

    /* ===== TELEMETRY ROWS ===== */
    .sw-telem-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .6875rem 0;
        border-bottom: 1px solid var(--border);
        transition: background .12s;
        border-radius: .5rem;
    }
    .sw-telem-row:last-child { border-bottom: none; }
    .sw-telem-row:hover {
        background: rgba(255,255,255,.018);
        padding-left: .625rem;
        padding-right: .625rem;
        margin: 0 -.625rem;
        border-bottom-color: transparent;
    }
    .sw-telem-row__left {}
    .sw-telem-loc {
        font-size: .8125rem;
        font-weight: 600;
        color: var(--text);
    }
    .sw-telem-meta {
        font-family: 'JetBrains Mono', monospace;
        font-size: .63rem;
        color: var(--text-dim);
        margin-top: .1rem;
    }
    .sw-telem-row__right { display: flex; align-items: center; gap: .875rem; }
    .sw-telem-val {
        font-family: 'JetBrains Mono', monospace;
        font-size: .9375rem;
        font-weight: 700;
        color: var(--cyan);
        white-space: nowrap;
    }
    .sw-telem-time {
        font-family: 'JetBrains Mono', monospace;
        font-size: .63rem;
        color: var(--text-dim);
        white-space: nowrap;
    }
    .sw-empty {
        border: 1px dashed var(--border);
        border-radius: .75rem;
        padding: 2.5rem 1.5rem;
        text-align: center;
        font-family: 'JetBrains Mono', monospace;
        font-size: .72rem;
        color: var(--text-dim);
        letter-spacing: .05em;
    }

    /* ===== ALERT ITEMS ===== */
    .sw-alert {
        border: 1px solid rgba(248,81,73,.18);
        background: rgba(248,81,73,.035);
        border-radius: .75rem;
        padding: .9375rem 1rem;
        margin-bottom: .625rem;
        transition: border-color .2s;
    }
    .sw-alert:last-child { margin-bottom: 0; }
    .sw-alert:hover { border-color: rgba(248,81,73,.38); }
    .sw-alert__hd {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .4rem;
    }
    .sw-alert__type {
        font-family: 'JetBrains Mono', monospace;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: var(--red);
    }
    .sw-alert__time {
        font-family: 'JetBrains Mono', monospace;
        font-size: .63rem;
        color: var(--text-dim);
    }
    .sw-alert__desc {
        font-size: .8125rem;
        color: var(--text-muted);
        margin-bottom: .35rem;
        line-height: 1.5;
    }
    .sw-alert__loc {
        font-family: 'JetBrains Mono', monospace;
        font-size: .63rem;
        color: var(--text-dim);
    }
    .sw-all-clear {
        border: 1px solid rgba(63,185,80,.18);
        background: rgba(63,185,80,.04);
        border-radius: .75rem;
        padding: 1.5rem 1rem;
        text-align: center;
        font-family: 'JetBrains Mono', monospace;
        font-size: .72rem;
        color: var(--green);
        letter-spacing: .08em;
    }

    /* ===== LOCATION CARDS ===== */
    .sw-loc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: .875rem;
    }
    .sw-loc {
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: .875rem;
        padding: 1.125rem 1.125rem;
        transition: border-color .2s, transform .15s;
    }
    .sw-loc:hover {
        border-color: rgba(63,185,80,.28);
        transform: translateY(-2px);
    }
    .sw-loc__name {
        font-size: .875rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: .2rem;
    }
    .sw-loc__addr {
        font-family: 'JetBrains Mono', monospace;
        font-size: .62rem;
        color: var(--text-dim);
        margin-bottom: .875rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sw-loc__foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .sw-loc__bins {
        font-family: 'JetBrains Mono', monospace;
        font-size: .68rem;
        color: var(--text-muted);
    }
    .sw-loc__gps {
        font-family: 'JetBrains Mono', monospace;
        font-size: .58rem;
        text-transform: uppercase;
        letter-spacing: .1em;
        padding: .175rem .5rem;
        border-radius: .25rem;
        border: 1px solid var(--border);
        color: var(--text-dim);
    }
    .sw-loc__gps--on {
        border-color: rgba(63,185,80,.25);
        color: var(--green);
        background: rgba(63,185,80,.07);
    }

    /* ===== BOTTOM 3-COL ===== */
    .sw-three {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .875rem;
    }
    @media (max-width: 1280px) { .sw-three { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px)  { .sw-three { grid-template-columns: 1fr; } }

    .sw-log {
        border: 1px solid var(--border);
        background: var(--panel);
        border-radius: .625rem;
        padding: .875rem 1rem;
        margin-bottom: .5rem;
        transition: border-color .2s;
    }
    .sw-log:last-child { margin-bottom: 0; }
    .sw-log:hover { border-color: var(--border-hi); }
    .sw-log--amber {
        border-color: rgba(210,153,34,.18);
        background: rgba(210,153,34,.03);
    }
    .sw-log__name {
        font-size: .8125rem;
        font-weight: 600;
        color: var(--text);
        margin-bottom: .2rem;
    }
    .sw-log__meta {
        font-family: 'JetBrains Mono', monospace;
        font-size: .62rem;
        color: var(--text-dim);
        margin-bottom: .35rem;
    }
    .sw-log__note {
        font-size: .75rem;
        color: var(--text-muted);
        line-height: 1.5;
    }
    .sw-log__note--amber { color: var(--amber); }

    /* Grid layout for two panels */
    .sw-two {
        display: grid;
        grid-template-columns: 1.3fr 0.7fr;
        gap: .875rem;
    }
    @media (max-width: 1280px) { .sw-two { grid-template-columns: 1fr; } }
</style>

@php
    $sensorTotal    = max($stats['sensors'], 1);
    $activePercent  = round(($stats['active_sensors']  / $sensorTotal) * 100);
    $silentPercent  = round(($stats['silent_sensors']  / $sensorTotal) * 100);
    $alertStatus    = $stats['alerts_open'] > 0 ? 'red' : 'green';
    $alertLabel     = $stats['alerts_open'] > 0 ? 'Alerts Active' : 'All Clear';
@endphp

<div class="sw-ops" wire:poll.{{ $refreshSeconds }}s>

    {{-- ── HERO ─────────────────────────────────────────────── --}}
    <div class="sw-hero">
        <div class="sw-hero__grid-bg"></div>
        <div class="sw-hero__glow"></div>
        <div class="sw-hero__glow-2"></div>

        <div class="sw-hero__body">
            <div class="sw-hero__left">
                <div class="sw-chip-row">
                    <span class="sw-chip sw-chip--green"><span class="dot"></span>System Online</span>
                    @if($stats['alerts_open'] > 0)
                        <span class="sw-chip sw-chip--red"><span class="dot"></span>{{ $stats['alerts_open'] }} Alert{{ $stats['alerts_open'] !== 1 ? 's' : '' }}</span>
                    @else
                        <span class="sw-chip sw-chip--green">All Clear</span>
                    @endif
                    @if($transmissionRate !== null)
                        <span class="sw-chip sw-chip--blue">TX&nbsp;{{ $transmissionRate }}%</span>
                    @endif
                </div>

                <h1 class="sw-hero__title">Smart Waste<br><em>Command Center</em></h1>

                <p class="sw-hero__sub">
                    Real-time fleet monitoring for IoT waste bins across all locations.
                    Sensor telemetry, overflow alerts, and collection logistics — unified.
                </p>

                <div class="sw-hero__meta-row">
                    <span class="sw-hero__meta-item">
                        LAST_PULSE &nbsp;<span>{{ $latestMeasurementAt ? $latestMeasurementAt->diffForHumans() : '—' }}</span>
                    </span>
                    <span class="sw-hero__meta-item">
                        POLL_INTERVAL &nbsp;<span>{{ $refreshSeconds }}s</span>
                    </span>
                    <span class="sw-hero__meta-item">
                        MAINT_DUE &nbsp;<span>{{ $stats['maintenance_due'] }}</span>
                    </span>
                </div>
            </div>

            <div class="sw-hero__right">
                <div class="sw-gauge">
                    <div class="sw-gauge__label">Active Sensors</div>
                    <div class="sw-gauge__row">
                        <span class="sw-gauge__val">{{ $stats['active_sensors'] }}</span>
                        <span class="sw-gauge__tag sw-gauge__tag--green">last 10 min</span>
                    </div>
                    <div class="sw-bar">
                        <div class="sw-bar__fill sw-bar__fill--green" style="width:{{ $activePercent }}%"></div>
                    </div>
                </div>

                <div class="sw-gauge">
                    <div class="sw-gauge__label">Silent Sensors</div>
                    <div class="sw-gauge__row">
                        <span class="sw-gauge__val">{{ $stats['silent_sensors'] }}</span>
                        <span class="sw-gauge__tag sw-gauge__tag--red">needs attention</span>
                    </div>
                    <div class="sw-bar">
                        <div class="sw-bar__fill sw-bar__fill--red" style="width:{{ $silentPercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STATS ────────────────────────────────────────────── --}}
    <div class="sw-divider"><span class="sw-divider__label">Fleet Overview</span><span class="sw-divider__line"></span></div>

    <div class="sw-stats">
        <div class="sw-stat sw-stat--green">
            <div class="sw-stat__val">{{ $stats['locations'] }}</div>
            <div class="sw-stat__name">Locations</div>
            <p class="sw-stat__meta">Operational hubs</p>
        </div>
        <div class="sw-stat sw-stat--blue">
            <div class="sw-stat__val">{{ $stats['bins'] }}</div>
            <div class="sw-stat__name">Bins</div>
            <p class="sw-stat__meta">Tracked containers</p>
        </div>
        <div class="sw-stat sw-stat--cyan">
            <div class="sw-stat__val">{{ $stats['sensors'] }}</div>
            <div class="sw-stat__name">Sensors</div>
            <p class="sw-stat__meta">{{ $stats['active_sensors'] }} active · {{ $stats['silent_sensors'] }} silent</p>
        </div>
        <div class="sw-stat sw-stat--{{ $stats['alerts_open'] > 0 ? 'red' : 'amber' }}">
            <div class="sw-stat__val">{{ $stats['alerts_open'] }}</div>
            <div class="sw-stat__name">Open Alerts</div>
            <p class="sw-stat__meta">{{ $stats['maintenance_due'] }} maintenance due</p>
        </div>
    </div>

    {{-- ── TELEMETRY + ALERTS ───────────────────────────────── --}}
    <div class="sw-divider" style="margin-top:1.75rem;"><span class="sw-divider__label">Live Feed</span><span class="sw-divider__line"></span></div>

    <div class="sw-two">
        <div class="sw-panel">
            <div class="sw-panel__hd">
                <div>
                    <p class="sw-panel__title">Live Telemetry</p>
                    <p class="sw-panel__sub">Streaming sensor readings</p>
                </div>
                <span class="sw-badge sw-badge--green">Auto-refresh</span>
            </div>
            @forelse ($recentMeasurements as $m)
                <div class="sw-telem-row">
                    <div class="sw-telem-row__left">
                        <div class="sw-telem-loc">{{ $m->sensor?->bin?->location?->name ?? 'Unknown' }}</div>
                        <div class="sw-telem-meta">
                            SNS_{{ $m->sensor?->sensor_id ?? '—' }}
                            &nbsp;·&nbsp;
                            BIN_{{ $m->sensor?->bin?->bin_id ?? '—' }}
                        </div>
                    </div>
                    <div class="sw-telem-row__right">
                        <span class="sw-telem-val">{{ number_format($m->value, 2) }}&nbsp;{{ $m->unit }}</span>
                        <span class="sw-telem-time">{{ $m->timestamp?->diffForHumans() ?? 'just now' }}</span>
                    </div>
                </div>
            @empty
                <div class="sw-empty">// No telemetry — push data to /api/sensor-data</div>
            @endforelse
        </div>

        <div class="sw-panel">
            <div class="sw-panel__hd">
                <div>
                    <p class="sw-panel__title">Active Alerts</p>
                    <p class="sw-panel__sub">Overflow &amp; anomalies</p>
                </div>
                <span class="sw-badge sw-badge--{{ $stats['alerts_open'] > 0 ? 'red' : 'green' }}">
                    {{ $stats['alerts_open'] }} open
                </span>
            </div>
            @forelse ($recentAlerts as $alert)
                <div class="sw-alert">
                    <div class="sw-alert__hd">
                        <span class="sw-alert__type">{{ $alert->type }}</span>
                        <span class="sw-alert__time">{{ $alert->timestamp?->diffForHumans() }}</span>
                    </div>
                    <p class="sw-alert__desc">{{ $alert->description }}</p>
                    <div class="sw-alert__loc">
                        BIN_{{ $alert->bin?->bin_id ?? '—' }}&nbsp;&nbsp;·&nbsp;&nbsp;{{ $alert->bin?->location?->name ?? 'Unknown' }}
                    </div>
                </div>
            @empty
                <div class="sw-all-clear">// ALL_CLEAR — no active alerts</div>
            @endforelse
        </div>
    </div>

    {{-- ── LOCATION GRID ────────────────────────────────────── --}}
    <div class="sw-divider" style="margin-top:1.75rem;"><span class="sw-divider__label">Location Pulse</span><span class="sw-divider__line"></span></div>

    <div class="sw-panel" style="animation-delay:.3s">
        <div class="sw-panel__hd">
            <div>
                <p class="sw-panel__title">Deployment Grid</p>
                <p class="sw-panel__sub">Coverage and bin density</p>
            </div>
            <span class="sw-badge sw-badge--green">{{ $stats['locations'] }} sites</span>
        </div>
        <div class="sw-loc-grid">
            @forelse ($locations as $location)
                <div class="sw-loc">
                    <div class="sw-loc__name">{{ $location->name }}</div>
                    <div class="sw-loc__addr">{{ $location->address ?? 'Address not set' }}</div>
                    <div class="sw-loc__foot">
                        <span class="sw-loc__bins">{{ $location->bins_count ?? 0 }} bins</span>
                        <span class="sw-loc__gps {{ $location->latitude !== null ? 'sw-loc__gps--on' : '' }}">
                            {{ $location->latitude !== null ? 'GPS ✓' : 'No GPS' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="sw-empty" style="grid-column:1/-1">// No locations — add sites to light up the grid</div>
            @endforelse
        </div>
    </div>

    {{-- ── BOTTOM 3-COL ─────────────────────────────────────── --}}
    <div class="sw-divider" style="margin-top:1.75rem;"><span class="sw-divider__label">Operations</span><span class="sw-divider__line"></span></div>

    <div class="sw-three">
        {{-- Collection Schedule --}}
        <div class="sw-panel" style="animation-delay:.35s">
            <div class="sw-panel__hd">
                <div>
                    <p class="sw-panel__title">Collection Schedule</p>
                    <p class="sw-panel__sub">Upcoming pickups</p>
                </div>
            </div>
            @forelse ($upcomingCollections as $schedule)
                <div class="sw-log">
                    <div class="sw-log__name">{{ $schedule->location?->name ?? 'Unknown' }}</div>
                    <div class="sw-log__meta">{{ $schedule->planned_time?->format('M d, Y · H:i') }}</div>
                    <div class="sw-log__note">Crew: {{ $schedule->collector_name }}</div>
                </div>
            @empty
                <div class="sw-empty">// No scheduled collections</div>
            @endforelse
        </div>

        {{-- Maintenance Log --}}
        <div class="sw-panel" style="animation-delay:.4s">
            <div class="sw-panel__hd">
                <div>
                    <p class="sw-panel__title">Maintenance Log</p>
                    <p class="sw-panel__sub">Sensor servicing events</p>
                </div>
                @if($stats['maintenance_due'] > 0)
                    <span class="sw-badge sw-badge--amber">{{ $stats['maintenance_due'] }} due</span>
                @endif
            </div>
            @forelse ($maintenanceLogs as $log)
                <div class="sw-log sw-log--amber">
                    <div class="sw-log__name">{{ $log->sensor?->bin?->location?->name ?? 'Unknown' }}</div>
                    <div class="sw-log__meta">
                        {{ $log->maintenance_date?->format('M d, Y') }}
                        @if($log->technician_name) · {{ $log->technician_name }} @endif
                    </div>
                    <div class="sw-log__note sw-log__note--amber">{{ $log->action_taken }}</div>
                </div>
            @empty
                <div class="sw-empty">// No maintenance events logged</div>
            @endforelse
        </div>

        {{-- Analysis Results --}}
        <div class="sw-panel" style="animation-delay:.45s">
            <div class="sw-panel__hd">
                <div>
                    <p class="sw-panel__title">Analysis Results</p>
                    <p class="sw-panel__sub">Anomaly detection output</p>
                </div>
            </div>
            @forelse ($analysisResults as $result)
                <div class="sw-log">
                    <div class="sw-log__name">{{ $result->analysis_type }}</div>
                    <div class="sw-log__meta">
                        {{ $result->bin?->location?->name ?? 'Unknown' }}
                        @if($result->timestamp) · {{ $result->timestamp->diffForHumans() }} @endif
                    </div>
                    <div class="sw-log__note">
                        {{ is_array($result->result_data)
                            ? json_encode($result->result_data)
                            : $result->result_data }}
                    </div>
                </div>
            @empty
                <div class="sw-empty">// Run analytics to surface insights</div>
            @endforelse
        </div>
    </div>

</div>
