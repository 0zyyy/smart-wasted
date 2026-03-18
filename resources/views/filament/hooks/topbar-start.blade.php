<div class="flex items-center gap-x-4 py-1 overflow-hidden">

    {{-- System title --}}
    <div class="hidden sm:flex flex-col">
        <h1 class="text-sm font-semibold tracking-tight text-gray-950 dark:text-white leading-none">
            Smart Waste Management
        </h1>
        <span class="text-[10px] font-mono text-gray-400 dark:text-[#484f58] tracking-[0.12em] uppercase leading-none mt-0.5">
            Operations Console
        </span>
    </div>

    {{-- Divider --}}
    <div class="hidden md:block h-6 w-px bg-gray-200 dark:bg-[#21262d]"></div>

    {{-- Live status chips --}}
    <div class="hidden md:flex items-center gap-x-2">
        {{-- Always-on system pulse --}}
        <span class="inline-flex items-center gap-1.5 rounded-md border border-emerald-300/40 dark:border-[rgba(63,185,80,0.25)] bg-emerald-50 dark:bg-[rgba(63,185,80,0.08)] px-2.5 py-1 text-[10px] font-mono font-semibold uppercase tracking-[0.12em] text-emerald-700 dark:text-[#3fb950]">
            <span class="relative flex h-1.5 w-1.5">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-500 dark:bg-[#3fb950] opacity-60"></span>
                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500 dark:bg-[#3fb950]"></span>
            </span>
            Online
        </span>

        {{-- TX rate (server-side; re-evaluates on Livewire poll) --}}
        @php
            $txRate = \App\Services\DashboardCacheService::getTransmissionRate();
        @endphp
        @if($txRate !== null)
            <span class="inline-flex items-center gap-1 rounded-md border border-sky-300/40 dark:border-[rgba(88,166,255,0.2)] bg-sky-50 dark:bg-[rgba(88,166,255,0.07)] px-2.5 py-1 text-[10px] font-mono font-semibold uppercase tracking-[0.12em] text-sky-700 dark:text-[#58a6ff]">
                TX {{ $txRate }}%
            </span>
        @endif
    </div>

</div>
