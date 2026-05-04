<x-filament-panels::page>
    <style>
        .report-page-container table:not(.signature-table), 
        .report-page-container table:not(.signature-table) th, 
        .report-page-container table:not(.signature-table) td {
            border: 1px solid black !important;
        }

        .rotated-header {
            white-space: nowrap;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            padding: 4px 2px !important;
            vertical-align: middle;
            text-align: left;
            height: 120px;
        }

        @media print {
            aside, header, .fi-topbar, .fi-sidebar {
                display: none !important;
            }
            .fi-main {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            @page {
                size: legal landscape;
                margin: 0; /* Removes browser header/footer text */
            }
            form, .fi-page-header, .report-toolbar, .no-print {
                display: none !important;
            }
            html, body, .fi-main, .fi-body, .fi-layout, .fi-page, .x-filament-panels::page {
                background-color: white !important;
                color: black !important;
            }
            .report-page-container, .printable-area {
                background-color: white !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            table, th, td {
                border: 1px solid black !important;
            }
            .printable-area {
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                border: none !important;
                border-radius: 0 !important;
            }
            .report-page-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0.5in !important; /* Add padding here so content doesn't get cut off when margin is 0 */
                border-radius: 0 !important;
            }
            .signature-table, .signature-table td {
                border: none !important;
            }
        }

        .signature-table, .signature-table td {
            border: none !important;
        }
    </style>

    {{-- ═══════════ FILTER CARD ═══════════ --}}
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
        {{-- Accent gradient strip --}}
        <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #0B9E4F 0%, #10B981 50%, #34D399 100%);"></div>

        <form wire:submit="generateReport" class="p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl" style="background: linear-gradient(135deg, #0B9E4F, #10B981);">
                    <x-heroicon-o-funnel class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Report Filters</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Select category, period, and field site to generate your report</p>
                </div>
            </div>

            {{ $this->form }}

            {{-- Action buttons --}}
            <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                <x-filament::button type="submit" icon="heroicon-o-document-magnifying-glass" size="lg">
                    Generate Preview
                </x-filament::button>
                
                @if($reportData)
                    <div class="h-8 w-px bg-gray-200 dark:bg-gray-700 mx-1"></div>
                    <x-filament::button color="info" icon="heroicon-o-printer" onclick="window.print()" size="sm">
                        Print / Save as PDF
                    </x-filament::button>
                    {{ $this->exportExcelAction }}
                    {{ $this->shareAction }}
                @endif
            </div>
        </form>
    </div>
    
    <x-filament-actions::modals />

    {{-- ═══════════ REPORT PREVIEW ═══════════ --}}
    @if($reportData)
        @php $siteCount = count($reportData); $currentIndex = 0; @endphp

        @foreach($reportData as $siteId => $siteData)
            @php 
                $siteRecords = $siteData['records']; 
                $reportFarms = $siteData['farms'];
                $currentIndex++;
                $siteName = $siteRecords->first()?->fieldSite?->name ?? 'Unknown Site';
            @endphp

            {{-- Site label badge (hidden on print) --}}
            <div class="no-print flex items-center gap-3 mt-8 mb-3">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold text-white" style="background: linear-gradient(135deg, #0B9E4F, #059669);">
                    <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                    {{ $siteName }}
                </div>
                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">{{ $currentIndex }} of {{ $siteCount }}</span>
            </div>

            {{-- Report page container --}}
            <div class="report-page-container overflow-x-auto bg-white shadow-xl rounded-xl border border-gray-200 dark:border-gray-700 text-black" style="page-break-after: always;">
                <div class="printable-area p-5 min-w-[1300px] mx-auto text-[11px] leading-tight">
                
                <!-- Header Section -->
                <div class="flex justify-center items-center gap-4 mb-3">
                    <!-- Logo placed beside text -->
                    <img src="{{ asset('images/PCA_DA_Logo.png') }}" class="h-16 w-16 object-contain" alt="PCA Logo">
                    
                    <!-- Text Block -->
                    <div class="text-center">
                        <h2 class="font-bold text-sm leading-tight">PHILIPPINE COCONUT AUTHORITY</h2>
                        <p class="font-bold text-xs uppercase">COCONUT HYBRIDIZATION PROJECT-CFIDP</p>
                        
                        @if($data['category'] === 'monthly_harvest')
                            <p class="uppercase text-xs mt-0.5">ON-FARM HYBRID SEEDNUT PRODUCTION</p>
                        @elseif($data['category'] === 'pollen_production')
                            <p class="uppercase text-xs mt-0.5">POLLEN PRODUCTION AND PROCESSING</p>
                        @elseif($data['category'] === 'hybrid_distribution')
                            <p class="uppercase text-xs mt-0.5">DISTRIBUTION OF HYBRID SEEDLINGS</p>
                        @elseif($data['category'] === 'nursery_operation')
                            <p class="uppercase text-xs mt-0.5">MONTHLY NURSERY OPERATION REPORT</p>
                        @elseif($data['category'] === 'terminal_report')
                            <p class="uppercase text-xs mt-0.5">TERMINAL REPORT FOR NURSERY OPERATIONS</p>
                        @endif

                        @php
                            $asOfDate = \Carbon\Carbon::create($data['year'], $data['month'] ?: 1, 1);
                            $isCumulative = ($data['export_range'] ?? 'single') === 'cumulative';
                            if ($data['year'] && !$data['month']) {
                                $asOfStr = in_array($data['category'], ['hybrid_distribution', 'nursery_operation', 'terminal_report']) ? 'as of end of ' . $data['year'] : 'For the year ' . $data['year'];
                            } elseif ($isCumulative) {
                                $asOfStr = in_array($data['category'], ['hybrid_distribution', 'nursery_operation', 'terminal_report']) ? 'Cumulative as of ' . $asOfDate->endOfMonth()->format('F d, Y') : 'For the months of January to ' . $asOfDate->format('F Y');
                            } else {
                                $asOfStr = in_array($data['category'], ['hybrid_distribution', 'nursery_operation', 'terminal_report']) ? 'as of ' . $asOfDate->endOfMonth()->format('F d, Y') : 'For the month of ' . $asOfDate->format('F Y');
                            }
                        @endphp
                        <p class="text-xs font-semibold underline mt-1">{{ $asOfStr }}</p>
                    </div>
                </div>

                <!-- Dynamic Table Content -->
                <div class="mb-4 w-full overflow-x-auto" style="margin-top: 50px !important;">
                @if($data['category'] === 'monthly_harvest')
                    @include('filament.pages.partials.reports.monthly_harvest', ['reportData' => $siteRecords, 'reportFarms' => $reportFarms])
                @elseif($data['category'] === 'pollen_production')
                    @include('filament.pages.partials.reports.pollen_production', ['reportData' => $siteRecords])
                @elseif($data['category'] === 'hybrid_distribution')
                    @include('filament.pages.partials.reports.hybrid_distribution', ['reportData' => $siteRecords])
                @elseif(in_array($data['category'], ['nursery_operation', 'terminal_report']))
                    @include('filament.pages.partials.reports.nursery_operation', ['reportData' => $siteRecords])
                @endif
            </div>

            <!-- Footer / Signatories Section -->
            @php
                $site = $siteRecords->first()->fieldSite ?? null;

                $selectedMonth = $data['month'] ?? null;
                $selectedYear = $data['year'] ?? null;

                $currentMonthRecords = $selectedMonth 
                    ? $siteRecords->filter(fn($r) => \Carbon\Carbon::parse($r->report_month)->month == $selectedMonth && \Carbon\Carbon::parse($r->report_month)->year == $selectedYear)
                    : $siteRecords;

                $statusOrder = ['draft' => 0, 'prepared' => 1, 'reviewed' => 2, 'noted' => 3];
                $minStatus = $currentMonthRecords->isNotEmpty()
                    ? $currentMonthRecords->min(fn($r) => $statusOrder[$r->status] ?? 0)
                    : 0;

                $refRecord = $currentMonthRecords->first();

                $showPrepared = $minStatus >= 1;
                $showReviewed = $minStatus >= 2;
                $showNoted = $minStatus >= 3;

                $prepUser = $showPrepared ? $refRecord?->preparedByUser : null;
                $revUser = $showReviewed ? $refRecord?->reviewedByUser : null;
                $notedUser = $showNoted ? $refRecord?->notedByUser : null;

                $prepName = $showPrepared ? strtoupper($site->prepared_by_name ?? $prepUser?->name ?? '_______________________') : '_______________________';
                $prepTitle = $site->prepared_by_title ?? $prepUser?->role_title ?? 'COS/Agriculturist';
                $prepSig = $showPrepared && $prepUser?->signature_image ? \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($prepUser->signature_image) : null;
                
                $revName = $showReviewed ? strtoupper($site->reviewed_by_name ?? $revUser?->name ?? '_______________________') : '_______________________';
                $revTitle = $site->reviewed_by_title ?? $revUser?->role_title ?? 'Senior Agriculturist';
                $revSig = $showReviewed && $revUser?->signature_image ? \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($revUser->signature_image) : null;
                
                $notedName = $showNoted ? strtoupper($site->noted_by_name ?? $notedUser?->name ?? '_______________________') : '_______________________';
                $notedTitle = $site->noted_by_title ?? $notedUser?->role_title ?? 'PCDM/Division Chief I';
                $notedSig = $showNoted && $notedUser?->signature_image ? \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($notedUser->signature_image) : null;
            @endphp
            <table class="w-full mt-12 text-center text-[11px] signature-table" style="table-layout: fixed;">
                <tr>
                    <td class="text-left align-top pl-8">{{ $site->prepared_by_label ?? 'Prepared by:' }}</td>
                    <td class="text-left align-top pl-8">{{ $site->reviewed_by_label ?? 'Reviewed by:' }}</td>
                    <td class="text-left align-top pl-8">{{ $site->noted_by_label ?? 'Noted by:' }}</td>
                </tr>
                <tr>
                    <td class="px-8 align-bottom" style="height: 80px;">
                        <div class="flex flex-col items-center justify-end h-full">
                            @if($prepSig)
                                <img src="{{ $prepSig }}" style="height: 50px; object-fit: contain; margin-bottom: -8px;" alt="Signature">
                            @endif
                            <p class="font-bold uppercase">{{ $prepName }}</p>
                            <div class="border-t border-black mt-1 pt-1 w-full">{{ $prepTitle }}</div>
                        </div>
                    </td>
                    <td class="px-8 align-bottom" style="height: 80px;">
                        <div class="flex flex-col items-center justify-end h-full">
                            @if($revSig)
                                <img src="{{ $revSig }}" style="height: 50px; object-fit: contain; margin-bottom: -8px;" alt="Signature">
                            @endif
                            <p class="font-bold uppercase">{{ $revName }}</p>
                            <div class="border-t border-black mt-1 pt-1 w-full">{{ $revTitle }}</div>
                        </div>
                    </td>
                    <td class="px-8 align-bottom" style="height: 80px;">
                        <div class="flex flex-col items-center justify-end h-full">
                            @if($notedSig)
                                <img src="{{ $notedSig }}" style="height: 50px; object-fit: contain; margin-bottom: -8px;" alt="Signature">
                            @endif
                            <p class="font-bold uppercase">{{ $notedName }}</p>
                            <div class="border-t border-black mt-1 pt-1 w-full">{{ $notedTitle }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        </div>
        @endforeach
    @endif
</x-filament-panels::page>
