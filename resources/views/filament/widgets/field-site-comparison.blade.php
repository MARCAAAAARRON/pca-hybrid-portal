<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header with title + filter controls --}}
        <div class="space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-scale class="h-5 w-5 text-primary-500" />
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                        Field Site Comparison
                    </h3>
                </div>
            </div>

            {{-- Filter Controls --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Period A: Month --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Period A — Month
                    </label>
                    <select wire:model.live="monthA"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500">
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
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500">
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
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500">
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
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500">
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

        {{-- Comparison Table --}}
        <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
                        <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                            rowspan="2">
                            Field Site
                        </th>
                        @foreach (['harvest', 'nursery', 'pollen', 'distribution', 'terminal', 'total'] as $cat)
                            <th colspan="3"
                                class="whitespace-nowrap px-2 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400
                                    {{ $cat === 'total' ? 'border-l-2 border-gray-300 dark:border-gray-600' : '' }}">
                                {{ $categoryLabels[$cat] }}
                            </th>
                        @endforeach
                    </tr>
                    <tr class="border-b border-gray-200 bg-gray-50/50 dark:border-gray-700 dark:bg-gray-800/30">
                        @foreach (['harvest', 'nursery', 'pollen', 'distribution', 'terminal', 'total'] as $cat)
                            <th class="whitespace-nowrap px-2 py-1.5 text-center text-[10px] font-medium text-blue-600 dark:text-blue-400
                                {{ $cat === 'total' ? 'border-l-2 border-gray-300 dark:border-gray-600' : '' }}">
                                A
                            </th>
                            <th class="whitespace-nowrap px-2 py-1.5 text-center text-[10px] font-medium text-emerald-600 dark:text-emerald-400">
                                B
                            </th>
                            <th class="whitespace-nowrap px-2 py-1.5 text-center text-[10px] font-medium text-gray-400 dark:text-gray-500">
                                Δ
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse ($rows as $index => $row)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/50 dark:bg-gray-800/20' }} transition hover:bg-primary-50/50 dark:hover:bg-primary-900/10">
                            <td class="whitespace-nowrap px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-m-map-pin class="h-4 w-4 text-primary-500 shrink-0" />
                                    {{ $row['name'] }}
                                </div>
                            </td>
                            @foreach (['harvest', 'nursery', 'pollen', 'distribution', 'terminal', 'total'] as $cat)
                                {{-- Period A value --}}
                                <td class="whitespace-nowrap px-2 py-2.5 text-center text-gray-700 dark:text-gray-300 tabular-nums
                                    {{ $cat === 'total' ? 'border-l-2 border-gray-200 dark:border-gray-700 font-semibold' : '' }}">
                                    {{ $row['periodA'][$cat] }}
                                </td>
                                {{-- Period B value --}}
                                <td class="whitespace-nowrap px-2 py-2.5 text-center text-gray-700 dark:text-gray-300 tabular-nums
                                    {{ $cat === 'total' ? 'font-semibold' : '' }}">
                                    {{ $row['periodB'][$cat] }}
                                </td>
                                {{-- Change indicator --}}
                                <td class="whitespace-nowrap px-2 py-2.5 text-center tabular-nums
                                    {{ $cat === 'total' ? 'font-semibold' : '' }}">
                                    @if ($row['change'][$cat] > 0)
                                        <span class="inline-flex items-center gap-0.5 text-emerald-600 dark:text-emerald-400">
                                            <x-heroicon-m-arrow-trending-up class="h-3.5 w-3.5" />
                                            +{{ $row['change'][$cat] }}
                                        </span>
                                    @elseif ($row['change'][$cat] < 0)
                                        <span class="inline-flex items-center gap-0.5 text-red-600 dark:text-red-400">
                                            <x-heroicon-m-arrow-trending-down class="h-3.5 w-3.5" />
                                            {{ $row['change'][$cat] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-chart-bar class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                                    <span>No field site data found for the selected periods.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- Totals footer --}}
                @if (count($rows) > 1)
                    <tfoot>
                        <tr class="border-t-2 border-gray-300 bg-gray-100 font-semibold dark:border-gray-600 dark:bg-gray-800">
                            <td class="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-m-calculator class="h-4 w-4 text-gray-500" />
                                    All Sites Total
                                </div>
                            </td>
                            @foreach (['harvest', 'nursery', 'pollen', 'distribution', 'terminal', 'total'] as $cat)
                                <td class="whitespace-nowrap px-2 py-3 text-center text-gray-900 dark:text-white tabular-nums
                                    {{ $cat === 'total' ? 'border-l-2 border-gray-300 dark:border-gray-600' : '' }}">
                                    {{ $totalsA[$cat] }}
                                </td>
                                <td class="whitespace-nowrap px-2 py-3 text-center text-gray-900 dark:text-white tabular-nums">
                                    {{ $totalsB[$cat] }}
                                </td>
                                <td class="whitespace-nowrap px-2 py-3 text-center tabular-nums">
                                    @if ($totalsChange[$cat] > 0)
                                        <span class="inline-flex items-center gap-0.5 text-emerald-600 dark:text-emerald-400">
                                            <x-heroicon-m-arrow-trending-up class="h-3.5 w-3.5" />
                                            +{{ $totalsChange[$cat] }}
                                        </span>
                                    @elseif ($totalsChange[$cat] < 0)
                                        <span class="inline-flex items-center gap-0.5 text-red-600 dark:text-red-400">
                                            <x-heroicon-m-arrow-trending-down class="h-3.5 w-3.5" />
                                            {{ $totalsChange[$cat] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Legend --}}
        <div class="mt-3 flex flex-wrap items-center gap-4 text-[11px] text-gray-400 dark:text-gray-500">
            <span class="flex items-center gap-1">
                <x-heroicon-m-arrow-trending-up class="h-3 w-3 text-emerald-500" /> Increase
            </span>
            <span class="flex items-center gap-1">
                <x-heroicon-m-arrow-trending-down class="h-3 w-3 text-red-500" /> Decrease
            </span>
            <span class="flex items-center gap-1">
                <span class="text-gray-400">—</span> No change
            </span>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
