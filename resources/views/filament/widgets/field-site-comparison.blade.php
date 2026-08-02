<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header with title + filter controls --}}
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <x-heroicon-o-scale class="h-5 w-5 text-primary-500" />
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                    Field Site Comparison
                </h3>
            </div>

            {{-- Filter Controls --}}
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                {{-- Period A: Month --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Period A — Month
                    </label>
                    <select wire:model.live="monthA"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach ($months as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Period A: Year --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Period A — Year
                    </label>
                    <select wire:model.live="yearA"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach ($yearOptions as $y => $label)
                            <option value="{{ $y }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Period B: Month --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Period B — Month
                    </label>
                    <select wire:model.live="monthB"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach ($months as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Period B: Year --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Period B — Year
                    </label>
                    <select wire:model.live="yearB"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach ($yearOptions as $y => $label)
                            <option value="{{ $y }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Period Labels --}}
            <div class="flex flex-wrap items-center gap-4 text-xs">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                    A: {{ $labelA }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    B: {{ $labelB }}
                </span>
            </div>
        </div>

        {{-- Charts Grid --}}
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Chart 1: Per-Site Comparison (Total Records) --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <h4 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <x-heroicon-m-map-pin class="h-4 w-4 text-primary-500" />
                    Total Records per Site
                </h4>
                <div style="position: relative; height: 300px;" wire:ignore>
                    <canvas id="siteComparisonChart"></canvas>
                </div>
            </div>

            {{-- Chart 2: Per-Category Comparison (Aggregated) --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <h4 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <x-heroicon-m-chart-bar class="h-4 w-4 text-primary-500" />
                    Records by Category (All Sites)
                </h4>
                <div style="position: relative; height: 300px;" wire:ignore>
                    <canvas id="categoryComparisonChart"></canvas>
                </div>
            </div>
        </div>
    </x-filament::section>

    {{-- Chart.js Rendering --}}
    @script
    <script>
        // Destroy existing chart instances if they exist (Livewire re-renders)
        if (window._siteChart) window._siteChart.destroy();
        if (window._catChart)  window._catChart.destroy();

        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        const textColor = isDark ? '#9ca3af' : '#6b7280';

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: textColor,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 16,
                        font: { size: 12, weight: '500' },
                    },
                },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#ffffff',
                    titleColor: isDark ? '#f3f4f6' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#374151',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                    displayColors: true,
                    usePointStyle: true,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: textColor, precision: 0, font: { size: 11 } },
                    grid: { color: gridColor },
                    border: { display: false },
                },
                x: {
                    ticks: { color: textColor, font: { size: 11 } },
                    grid: { display: false },
                    border: { display: false },
                },
            },
        };

        // ── Chart 1: Per-Site ──
        const siteCtx = document.getElementById('siteComparisonChart');
        if (siteCtx) {
            window._siteChart = new Chart(siteCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($siteLabels),
                    datasets: [
                        {
                            label: @json($labelA),
                            data: @json($siteDataA),
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: '#3b82f6',
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                        {
                            label: @json($labelB),
                            data: @json($siteDataB),
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: '#10b981',
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                    ],
                },
                options: commonOptions,
            });
        }

        // ── Chart 2: Per-Category ──
        const catCtx = document.getElementById('categoryComparisonChart');
        if (catCtx) {
            window._catChart = new Chart(catCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($catLabels),
                    datasets: [
                        {
                            label: @json($labelA),
                            data: @json($catDataA),
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: '#3b82f6',
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                        {
                            label: @json($labelB),
                            data: @json($catDataB),
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: '#10b981',
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                    ],
                },
                options: commonOptions,
            });
        }
    </script>
    @endscript
</x-filament-widgets::widget>
