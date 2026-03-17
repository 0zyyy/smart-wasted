# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Smart Waste Management System** - An IoT-based waste bin monitoring platform that tracks fill levels and weight using ESP32 microcontrollers with load cell and ultrasonic sensors. The system provides real-time dashboards, automated alerting, and fleet management for smart waste bins across multiple locations.

**Tech Stack:**
- Laravel 12 (backend framework)
- Filament 5.1 (admin panel)
- Tailwind CSS 4 + Vite 7 (frontend)
- PHPUnit (testing)
- SQLite (default database, MySQL/PostgreSQL supported)

## Common Commands

### Development
```bash
# Initial setup (install dependencies, configure .env, migrate, build assets)
composer setup

# Start development server with queue, logs, and Vite (all-in-one)
composer dev

# Run tests
composer test
# Or run a single test
php artisan test --filter test_it_accepts_get_payload

# Build production assets
npm run build
# Development assets
npm run dev

# Code style checking (Laravel Pint)
./vendor/bin/pint

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Database
```bash
# Run migrations
php artisan migrate

# Create a new migration
php artisan make:migration create_table_name

# Fresh migration (WARNING: destroys data)
php artisan migrate:fresh
```

### Filament Admin
```bash
# Create a new Filament resource
php artisan make:filament-resource ResourceName

# Create a widget
php artisan make:filament-widget WidgetName

# Clear filament cache
php artisan filament:cache-clear
```

## Architecture

### Core Domain Model Hierarchy

The data model follows a hierarchical structure: **Location → Bin → Sensor → Measurement**

- **Location**: Physical site (e.g., "BB102", "AA107") where bins are deployed
- **Bin**: Container with type (Organic, Anorganic, B3) and capacity, located at a Location
- **Sensor**: Device attached to a bin (Loadcell for weight, Ultrasonic for volume)
- **Measurement**: Time-series sensor readings with value and unit
- **Alert**: Generated automatically (e.g., overflow ≥80%) or manually, with status tracking
- **AlertActivity**: Audit log of all alert state changes

### Data Flow: IoT Ingestion

ESP32 devices send sensor data to `/api/sensor-data` (supports both GET and POST for legacy compatibility):

**Request Formats:**
```
GET: /api/sensor-data?lokasi=organik&berat=1250&volume=75&device=BB102
POST: /api/sensor-data { "bin_type": "organik", "weight": 1250, "volume": 75, "location": "BB102" }
```

**Processing Pipeline (SensorDataController):**
1. Normalizes bin_type input (e.g., "organik" → "Organic")
2. Validates location against whitelist: BB102, BB202, AA107, AA108
3. Finds bin by location + bin_type combination
4. Creates measurements for both sensors (Loadcell, Ultrasonic)
5. Deduplicates identical payloads within 15-second window
6. Auto-generates "Overflow" alerts when volume ≥80%
7. Busts dashboard cache if new data was created

**Key Classes:**
- `StoreSensorDataRequest`: Validation and normalization
- `SensorDataController`: Main ingestion logic
- `DashboardCacheService`: Busts cache on new data

### Dashboard & Caching

The Filament dashboard uses aggressive caching to handle real-time telemetry efficiently:

**Cache Keys (30-second TTL):**
- `sw:dash:stats`: Core stats (sensor counts, maintenance due, open alerts)
- `sw:dash:sensor_health`: Active vs silent sensors (10-minute freshness window)
- `sw:dash:location_fill`: Fill percentages per location for charts
- `sw:dash:transmission_rate`: Data transmission success rate (last hour)

**Important:** Always call `DashboardCacheService::bust()` after creating/modifying telemetry data.

**Widgets (load order):**
1. `StatsOverviewWidget`: Key metrics with badges
2. `LiveTelemetryWidget`: Real-time measurements
3. `OpenAlertsQueueWidget`: Queued alerts needing attention
4. `SensorHealthWidget`: Doughnut chart of active/silent sensors
5. `LocationFillWidget`: Bar chart of fill percentages per location

### Alert System with Activity Logging

Alerts follow a state machine with full audit trail:

**States:**
- `open` → `acknowledged` → `resolved`
- Auto-resets to `open` when re-opened

**Activity Logging:**
```php
$alert->logActivity(
    action: 'acknowledged',
    note: 'Alert acknowledged from dashboard.',
    actorId: auth()->id(),
    meta: ['context' => 'value']
);
```

**Model Events:** Alert model uses `static::saving()` to auto-sync `is_resolved`, `resolved_at`, and `acknowledged_at` based on status changes.

## Important Conventions

### Database
- All models use **custom primary keys** (e.g., `measurement_id`, `bin_id`), NOT default `id`
- Foreign keys explicitly reference custom PKs in relationships
- Timestamps use `datetime` cast; measurements use UTC

### API Validation
- Bin types: `Organic`, `Anorganic`, `B3`
- Locations whitelist: `BB102`, `BB202`, `AA107`, `AA108` (expandable)
- Volume: 0-100 range (percentage)
- Weight: non-negative numeric (grams)

### Filament Resources
- Navigation groups: `Telemetry` (alerts, measurements, analysis), `Operations` (locations, bins, sensors, schedules)
- Custom detail slides-overs for complex records (see AlertResource)
- Quick actions in table rows for common state changes

### Testing
- Use `RefreshDatabase` trait for feature tests
- Create helper methods for test data setup (see `SensorDataControllerTest::createBinWithSensors()`)
- Test both GET and POST payloads for IoT endpoints
- Verify deduplication behavior (15-second window)

## File Structure Notes

```
app/
├── Filament/
│   ├── Admin/
│   │   ├── Pages/
│   │   │   └── Dashboard.php          # Custom dashboard with widgets
│   │   ├── Resources/                  # Filament CRUD resources
│   │   └── Widgets/                    # Dashboard widgets
│   └── Pages/
│       └── SmartWasteDashboard.php    # Public dashboard
├── Http/
│   ├── Controllers/
│   │   └── Api/                        # IoT endpoints
│   └── Requests/
│       └── StoreSensorDataRequest.php  # IoT validation
├── Models/                              # Eloquent models with custom PKs
├── Services/
│   └── DashboardCacheService.php       # Dashboard caching layer
└── Events/                              # Domain events
```

## Recent Enhancements

- **Alert Activities**: Full audit trail for alert lifecycle
- **Performance Indexes**: Database indexes for common queries
- **Operational Fields**: `assigned_to`, `acknowledged_by`, `last_seen_at` on alerts
- **Widget Polling**: Real-time dashboard updates (10s default)

## Development Workflow

When adding new sensor types or expanding to new locations:
1. Update `StoreSensorDataRequest::VALID_LOCATIONS` whitelist
2. Ensure location + bin_type combination exists in database
3. Add sensor type to `$sensors` loop in `SensorDataController`
4. Update dashboard widgets to display new data
5. Bust appropriate cache keys in `DashboardCacheService`

When modifying alert logic:
1. Update Alert model constants if adding new statuses/severities
2. Add activity logging: `$alert->logActivity()`
3. Test state transitions in both UI and API
4. Update AlertResource table filters if needed

<!-- rtk-instructions v2 -->
# RTK (Rust Token Killer) - Token-Optimized Commands

## Golden Rule

**Always prefix commands with `rtk`**. If RTK has a dedicated filter, it uses it. If not, it passes through unchanged. This means RTK is always safe to use.

**Important**: Even in command chains with `&&`, use `rtk`:
```bash
# ❌ Wrong
git add . && git commit -m "msg" && git push

# ✅ Correct
rtk git add . && rtk git commit -m "msg" && rtk git push
```

## RTK Commands by Workflow

### Build & Compile (80-90% savings)
```bash
rtk cargo build         # Cargo build output
rtk cargo check         # Cargo check output
rtk cargo clippy        # Clippy warnings grouped by file (80%)
rtk tsc                 # TypeScript errors grouped by file/code (83%)
rtk lint                # ESLint/Biome violations grouped (84%)
rtk prettier --check    # Files needing format only (70%)
rtk next build          # Next.js build with route metrics (87%)
```

### Test (90-99% savings)
```bash
rtk cargo test          # Cargo test failures only (90%)
rtk vitest run          # Vitest failures only (99.5%)
rtk playwright test     # Playwright failures only (94%)
rtk test <cmd>          # Generic test wrapper - failures only
```

### Git (59-80% savings)
```bash
rtk git status          # Compact status
rtk git log             # Compact log (works with all git flags)
rtk git diff            # Compact diff (80%)
rtk git show            # Compact show (80%)
rtk git add             # Ultra-compact confirmations (59%)
rtk git commit          # Ultra-compact confirmations (59%)
rtk git push            # Ultra-compact confirmations
rtk git pull            # Ultra-compact confirmations
rtk git branch          # Compact branch list
rtk git fetch           # Compact fetch
rtk git stash           # Compact stash
rtk git worktree        # Compact worktree
```

Note: Git passthrough works for ALL subcommands, even those not explicitly listed.

### GitHub (26-87% savings)
```bash
rtk gh pr view <num>    # Compact PR view (87%)
rtk gh pr checks        # Compact PR checks (79%)
rtk gh run list         # Compact workflow runs (82%)
rtk gh issue list       # Compact issue list (80%)
rtk gh api              # Compact API responses (26%)
```

### JavaScript/TypeScript Tooling (70-90% savings)
```bash
rtk pnpm list           # Compact dependency tree (70%)
rtk pnpm outdated       # Compact outdated packages (80%)
rtk pnpm install        # Compact install output (90%)
rtk npm run <script>    # Compact npm script output
rtk npx <cmd>           # Compact npx command output
rtk prisma              # Prisma without ASCII art (88%)
```

### Files & Search (60-75% savings)
```bash
rtk ls <path>           # Tree format, compact (65%)
rtk read <file>         # Code reading with filtering (60%)
rtk grep <pattern>      # Search grouped by file (75%)
rtk find <pattern>      # Find grouped by directory (70%)
```

### Analysis & Debug (70-90% savings)
```bash
rtk err <cmd>           # Filter errors only from any command
rtk log <file>          # Deduplicated logs with counts
rtk json <file>         # JSON structure without values
rtk deps                # Dependency overview
rtk env                 # Environment variables compact
rtk summary <cmd>       # Smart summary of command output
rtk diff                # Ultra-compact diffs
```

### Infrastructure (85% savings)
```bash
rtk docker ps           # Compact container list
rtk docker images       # Compact image list
rtk docker logs <c>     # Deduplicated logs
rtk kubectl get         # Compact resource list
rtk kubectl logs        # Deduplicated pod logs
```

### Network (65-70% savings)
```bash
rtk curl <url>          # Compact HTTP responses (70%)
rtk wget <url>          # Compact download output (65%)
```

### Meta Commands
```bash
rtk gain                # View token savings statistics
rtk gain --history      # View command history with savings
rtk discover            # Analyze Claude Code sessions for missed RTK usage
rtk proxy <cmd>         # Run command without filtering (for debugging)
rtk init                # Add RTK instructions to CLAUDE.md
rtk init --global       # Add RTK to ~/.claude/CLAUDE.md
```

## Token Savings Overview

| Category | Commands | Typical Savings |
|----------|----------|-----------------|
| Tests | vitest, playwright, cargo test | 90-99% |
| Build | next, tsc, lint, prettier | 70-87% |
| Git | status, log, diff, add, commit | 59-80% |
| GitHub | gh pr, gh run, gh issue | 26-87% |
| Package Managers | pnpm, npm, npx | 70-90% |
| Files | ls, read, grep, find | 60-75% |
| Infrastructure | docker, kubectl | 85% |
| Network | curl, wget | 65-70% |

Overall average: **60-90% token reduction** on common development operations.
<!-- /rtk-instructions -->

<!-- gitnexus:start -->
# GitNexus — Code Intelligence

This project is indexed by GitNexus as **smart-wasted** (3356 symbols, 10915 relationships, 280 execution flows). Use the GitNexus MCP tools to understand code, assess impact, and navigate safely.

> If any GitNexus tool warns the index is stale, run `npx gitnexus analyze` in terminal first.

## Always Do

- **MUST run impact analysis before editing any symbol.** Before modifying a function, class, or method, run `gitnexus_impact({target: "symbolName", direction: "upstream"})` and report the blast radius (direct callers, affected processes, risk level) to the user.
- **MUST run `gitnexus_detect_changes()` before committing** to verify your changes only affect expected symbols and execution flows.
- **MUST warn the user** if impact analysis returns HIGH or CRITICAL risk before proceeding with edits.
- When exploring unfamiliar code, use `gitnexus_query({query: "concept"})` to find execution flows instead of grepping. It returns process-grouped results ranked by relevance.
- When you need full context on a specific symbol — callers, callees, which execution flows it participates in — use `gitnexus_context({name: "symbolName"})`.

## When Debugging

1. `gitnexus_query({query: "<error or symptom>"})` — find execution flows related to the issue
2. `gitnexus_context({name: "<suspect function>"})` — see all callers, callees, and process participation
3. `READ gitnexus://repo/smart-wasted/process/{processName}` — trace the full execution flow step by step
4. For regressions: `gitnexus_detect_changes({scope: "compare", base_ref: "main"})` — see what your branch changed

## When Refactoring

- **Renaming**: MUST use `gitnexus_rename({symbol_name: "old", new_name: "new", dry_run: true})` first. Review the preview — graph edits are safe, text_search edits need manual review. Then run with `dry_run: false`.
- **Extracting/Splitting**: MUST run `gitnexus_context({name: "target"})` to see all incoming/outgoing refs, then `gitnexus_impact({target: "target", direction: "upstream"})` to find all external callers before moving code.
- After any refactor: run `gitnexus_detect_changes({scope: "all"})` to verify only expected files changed.

## Never Do

- NEVER edit a function, class, or method without first running `gitnexus_impact` on it.
- NEVER ignore HIGH or CRITICAL risk warnings from impact analysis.
- NEVER rename symbols with find-and-replace — use `gitnexus_rename` which understands the call graph.
- NEVER commit changes without running `gitnexus_detect_changes()` to check affected scope.

## Tools Quick Reference

| Tool | When to use | Command |
|------|-------------|---------|
| `query` | Find code by concept | `gitnexus_query({query: "auth validation"})` |
| `context` | 360-degree view of one symbol | `gitnexus_context({name: "validateUser"})` |
| `impact` | Blast radius before editing | `gitnexus_impact({target: "X", direction: "upstream"})` |
| `detect_changes` | Pre-commit scope check | `gitnexus_detect_changes({scope: "staged"})` |
| `rename` | Safe multi-file rename | `gitnexus_rename({symbol_name: "old", new_name: "new", dry_run: true})` |
| `cypher` | Custom graph queries | `gitnexus_cypher({query: "MATCH ..."})` |

## Impact Risk Levels

| Depth | Meaning | Action |
|-------|---------|--------|
| d=1 | WILL BREAK — direct callers/importers | MUST update these |
| d=2 | LIKELY AFFECTED — indirect deps | Should test |
| d=3 | MAY NEED TESTING — transitive | Test if critical path |

## Resources

| Resource | Use for |
|----------|---------|
| `gitnexus://repo/smart-wasted/context` | Codebase overview, check index freshness |
| `gitnexus://repo/smart-wasted/clusters` | All functional areas |
| `gitnexus://repo/smart-wasted/processes` | All execution flows |
| `gitnexus://repo/smart-wasted/process/{name}` | Step-by-step execution trace |

## Self-Check Before Finishing

Before completing any code modification task, verify:
1. `gitnexus_impact` was run for all modified symbols
2. No HIGH/CRITICAL risk warnings were ignored
3. `gitnexus_detect_changes()` confirms changes match expected scope
4. All d=1 (WILL BREAK) dependents were updated

## CLI

- Re-index: `npx gitnexus analyze`
- Check freshness: `npx gitnexus status`
- Generate docs: `npx gitnexus wiki`

<!-- gitnexus:end -->
