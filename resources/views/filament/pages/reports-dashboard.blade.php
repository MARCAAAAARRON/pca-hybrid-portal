<x-filament-panels::page>
    @php
        $siteKeys = $reportData ? array_keys($reportData) : [];
        $siteCount = count($siteKeys);
    @endphp

    <style>
        /* ── Report Table Borders ── */
        .report-page-container table:not(.signature-table),
        .report-page-container table:not(.signature-table) th,
        .report-page-container table:not(.signature-table) td { border: 1px solid black !important; }
        .rotated-header { white-space: nowrap; writing-mode: vertical-rl; transform: rotate(180deg); padding: 4px 2px !important; vertical-align: middle; text-align: left; height: 120px; }
        .signature-table, .signature-table td { border: none !important; }

        /* ── Dark Mode Fix ── */
        .report-page-container { background-color: white !important; color: black !important; }
        .report-page-container, .report-page-container *, .report-page-container p,
        .report-page-container h2, .report-page-container h3, .report-page-container td,
        .report-page-container th, .report-page-container span { color: black !important; border-color: black !important; }
        .report-page-container .bg-gray-100 { background-color: #f3f4f6 !important; }
        .report-page-container .bg-gray-50 { background-color: #f9fafb !important; }
        .report-page-container thead tr, .report-page-container .bg-\[\#0B9E4F\] { background-color: #0B9E4F !important; }
        .report-page-container thead tr th, .report-page-container thead tr td, .report-page-container .text-white { color: white !important; }



        /* ── Page visibility: only active page is shown on screen ── */
        .report-site-page { display: none; }
        .report-site-page.is-active { display: block; }



        /* ── Nav Arrows ── */
        .nav-arrow { display: inline-flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 9999px; border: 2px solid #d1d5db; background: white; cursor: pointer; transition: all 0.2s; color: #374151; }
        .nav-arrow:hover:not(:disabled) { background: #0B9E4F; border-color: #0B9E4F; color: white; transform: scale(1.1); }
        .nav-arrow:disabled { opacity: 0.35; cursor: not-allowed; }
        .dark .nav-arrow { background: #1f2937; border-color: #4b5563; color: #d1d5db; }

        /* ── Print styles (only used as fallback) ── */
        @media print {
            .no-print { display: none !important; }
        }

        /* ── Hide Filament notifications when modal is open ── */
        .modal-open .fi-notifications { display: none !important; }
    </style>

    {{-- ═══════════ FILTER CARD ═══════════ --}}
    <div class="no-print relative rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">

        <div class="h-1.5 w-full rounded-t-2xl" style="background: linear-gradient(90deg, #0B9E4F 0%, #10B981 50%, #34D399 100%);"></div>
        <form wire:submit="generateReport" class="p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl" style="background: linear-gradient(135deg, #0B9E4F, #10B981);">
                    <x-heroicon-o-funnel class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Report Filters</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Select one or more categories, period, and field site to generate your report</p>
                </div>
            </div>
            {{ $this->form }}
            <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                @php $catCount = count($this->data['categories'] ?? []); @endphp
                <x-filament::button type="submit" icon="{{ $catCount > 1 ? 'heroicon-o-squares-2x2' : 'heroicon-o-document-magnifying-glass' }}" size="lg" color="{{ $catCount > 1 ? 'success' : 'primary' }}" wire:loading.attr="disabled" wire:target="generateReport" class="disabled:opacity-70 disabled:cursor-not-allowed">
                    {{ $catCount > 1 ? 'Generate Report Package (' . $catCount . ' categories)' : 'Generate Report' }}
                </x-filament::button>

                @if(!empty($reportData) || $fullPackageMode)
                    <x-filament::button color="gray" icon="heroicon-o-eye" size="sm" wire:click="openReportModal" wire:loading.attr="disabled" wire:target="openReportModal, generateReport" class="disabled:opacity-50 disabled:cursor-not-allowed ml-auto">
                        View Report
                    </x-filament::button>
                @endif
            </div>
        </form>
    </div>

    <div class="no-print">
        <x-filament-actions::modals />
    </div>

    {{-- ═══════════ FLOATING REPORT MODAL ═══════════ --}}
    @if($showModal && (!empty($reportData) || $fullPackageMode))
        {{-- Hide notifications while modal is open --}}
        <script>document.documentElement.classList.add('modal-open');</script>

        @teleport('body')
        <div class="fixed inset-0 flex flex-col bg-white dark:bg-slate-900" style="z-index: 30 !important;" wire:click.self="closeModal">
            <div class="relative flex flex-col overflow-hidden w-full h-full bg-white dark:bg-slate-900" style="z-index: 31 !important;"
                 @keydown.escape.window="$wire.closeModal()"
                 @keydown.left.window="$wire.prevPage()"
                 @keydown.right.window="$wire.nextPage()">

                {{-- Modal Action Loading Overlay (Excel, Share, Navigation, Exit) --}}
                <div wire:loading wire:target="exportExcelAction, exportPdfAction, shareAction, goToPage, switchCategory, generateReport, firstPage, lastPage, prevPage, nextPage, closeModal" class="absolute inset-0 z-[10002] flex flex-col items-center justify-center bg-white/60 dark:bg-gray-900/60 backdrop-blur-md">
                    <div class="flex flex-col items-center p-8 rounded-3xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-100 dark:border-gray-700">
                        <x-filament::loading-indicator class="text-[#0b9e4f] mb-4" style="width: 54px; height: 54px;" />
                        <span class="text-lg font-bold text-[#0b9e4f] tracking-tight">Processing request...</span>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium text-center">Please wait a moment</p>
                    </div>
                </div>

                {{-- JS Print Loading Overlay --}}
                <div id="js-print-loader" style="display: none;" class="absolute inset-0 z-[10000] flex flex-col items-center justify-center bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm rounded-xl">
                    <x-filament::loading-indicator class="w-10 h-10 text-emerald-600 dark:text-emerald-400 mb-3" />
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Preparing print preview, please wait...</span>
                </div>

                {{-- Modal Header --}}
                <div class="shrink-0 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-gray-700">

                    {{-- CATEGORY TABS (Multi-category mode: shows only user-selected categories) --}}
                    @if($fullPackageMode && count($selectedCategories) > 1)
                    @php
                        $categoryShortLabels = [
                            'monthly_harvest' => 'Monthly Harvest',
                            'pollen_production' => 'Pollen Prod.',
                            'hybrid_distribution' => 'Hybrid Dist.',
                            'nursery_operation' => 'Nursery Ops.',
                            'terminal_report' => 'Terminal',
                        ];
                    @endphp
                    <div class="flex items-center overflow-x-auto border-b border-gray-100 dark:border-gray-700/50 px-4 pt-2" style="scrollbar-width: none;">
                        @foreach($selectedCategories as $cat)
                            @php
                                $catLabel = $categoryShortLabels[$cat] ?? $cat;
                                $catHasData = !empty($fullPackageData[$cat]);
                            @endphp
                            <button
                                wire:click="switchCategory('{{ $cat }}')"
                                wire:loading.attr="disabled"
                                wire:target="switchCategory, goToPage, prevPage, nextPage, generateReport"
                                class="flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold whitespace-nowrap transition-all border-b-2 shrink-0
                                    {{ $activeCategory === $cat
                                        ? 'border-[#0b9e4f] text-[#0b9e4f]'
                                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}"
                            >
                                {{ $catLabel }}
                                @if(!$catHasData)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 font-medium">No data</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    @endif

                    {{-- TITLE + FARM TABS + ACTION BUTTONS ROW --}}
                    <div class="flex items-center justify-between px-6 py-3">

                        {{-- Left: Icon + Title + Subtitle --}}
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background: linear-gradient(135deg, #0B9E4F, #10B981);">
                                <x-heroicon-o-document-chart-bar class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $fullPackageMode ? 'Report Package (' . count($selectedCategories) . ' categories)' : 'Report Preview' }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                    @php
                                        $currentSiteId = $siteKeys[$currentPage] ?? null;
                                        $safeSiteId = $currentSiteId !== null ? (string)$currentSiteId : '';
                                        $currentSiteName = $safeSiteId !== '' ? ($siteNames[$safeSiteId] ?? $reportData[$currentSiteId]['records']->first()?->fieldSite?->name ?? 'Unknown Site') : '';
                                    @endphp
                                    <x-heroicon-o-map-pin class="w-3 h-3 shrink-0" /> {{ $currentSiteName }}
                                    @if($siteCount > 1)
                                        <span class="mx-1 text-gray-300 dark:text-gray-600">|</span>
                                        @if($batchMode || $fullPackageMode)
                                            <span class="inline-flex items-center gap-0.5 font-semibold" style="color: #0b9e4f;">
                                                <x-heroicon-m-squares-2x2 class="w-3 h-3" /> {{ $fullPackageMode ? 'Multi-Category' : 'Batch' }} &mdash; {{ $siteCount }} Sites
                                            </span>
                                        @else
                                            <span class="font-semibold" style="color: #0b9e4f;">Page {{ $currentPage + 1 }} of {{ $siteCount }}</span>
                                        @endif
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Right: Farm Tabs + Arrow Nav + Actions --}}
                        <div class="flex items-center gap-3 min-w-0">

                            @if($siteCount > 1)
                                {{-- Named Farm Tabs --}}
                                <div class="flex items-center gap-1 max-w-xs overflow-x-auto pb-0.5" style="scrollbar-width: none;">
                                    @foreach($siteKeys as $idx => $tabSiteId)
                                        @php
                                            $tabName = $siteNames[$tabSiteId] ?? ($reportData[$tabSiteId]['records']->first()?->fieldSite?->name ?? 'Site ' . ($idx + 1));
                                            $shortName = mb_strlen($tabName) > 16 ? mb_substr($tabName, 0, 14) . '…' : $tabName;
                                        @endphp
                                        <button
                                            wire:click="goToPage({{ $idx }})"
                                            wire:loading.attr="disabled"
                                            wire:target="goToPage, switchCategory, prevPage, nextPage, generateReport"
                                            title="{{ $tabName }}"
                                            class="px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all duration-200 shrink-0
                                                {{ $currentPage === $idx
                                                    ? 'text-white shadow-md'
                                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                                            style="{{ $currentPage === $idx ? 'background-color: #0b9e4f;' : '' }}"
                                        >
                                            {{ $shortName }}
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Compact Prev / Next arrows --}}
                                <div class="flex items-center gap-0.5 shrink-0">
                                    <button wire:click="prevPage" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition disabled:opacity-30 disabled:cursor-not-allowed" {{ $currentPage <= 0 ? 'disabled' : '' }} wire:loading.attr="disabled" wire:target="goToPage, switchCategory, prevPage, nextPage" title="Previous">
                                        <x-heroicon-m-chevron-left class="w-4 h-4" />
                                    </button>
                                    <button wire:click="nextPage" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition disabled:opacity-30 disabled:cursor-not-allowed" {{ $currentPage >= $siteCount - 1 ? 'disabled' : '' }} wire:loading.attr="disabled" wire:target="goToPage, switchCategory, prevPage, nextPage" title="Next">
                                        <x-heroicon-m-chevron-right class="w-4 h-4" />
                                    </button>
                                </div>
                            @endif

                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-2 border-l border-gray-200 dark:border-gray-700 pl-3 shrink-0">
                                <x-filament::button color="info" icon="heroicon-o-printer" onclick="printAllPages()" size="sm">
                                    Print
                                </x-filament::button>

                                {{ $this->exportPdfAction }}

                                {{ $this->exportExcelAction }}
                                {{ $this->shareAction }}

                                <x-filament::button color="danger" icon="heroicon-o-x-mark" wire:click="closeModal" size="sm" wire:loading.attr="disabled" wire:target="exportExcelAction, shareAction, goToPage, switchCategory, prevPage, nextPage, closeModal">
                                    Close
                                </x-filament::button>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Modal Body: ALL pages are in the DOM, only active one is visible on screen --}}
                <div class="flex-1 overflow-auto p-6">
                    @if(empty($reportData))
                        <div class="flex flex-col items-center justify-center h-full min-h-[300px] text-center text-gray-500">
                            <div class="w-16 h-16 mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                <x-heroicon-o-document-magnifying-glass class="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">No records found</h3>
                            <p class="text-sm mt-1 max-w-sm">There is no data available for this category in the selected period.</p>
                        </div>
                    @else
                        @foreach($siteKeys as $pageIdx => $siteId)
                            @php
                                $pageSiteData = $reportData[$siteId];
                                $pageSiteRecords = $pageSiteData['records'];
                                $pageReportFarms = $pageSiteData['farms'];
                            @endphp

                            <div class="report-site-page {{ $pageIdx === $currentPage ? 'is-active' : '' }}">
                            <div class="report-page-container overflow-x-auto bg-white rounded-none text-black">
                                <div class="printable-area py-8 min-w-[1500px] mx-auto text-[11px] leading-tight">
                                    <div class="flex w-full">
                                        <div class="w-5 shrink-0 no-print"></div>
                                        <div class="flex-1 px-4">
                                            {{-- PCA Header --}}
                                            <div class="flex justify-center items-center gap-4 mb-3">
                                                <img src="{{ asset('images/PCA_DA_Logo.png') }}" class="h-16 w-16 object-contain" alt="PCA Logo">
                                                <div class="text-center">
                                                    <h2 class="font-bold text-sm leading-tight">PHILIPPINE COCONUT AUTHORITY</h2>
                                                    <p class="font-bold text-xs uppercase">COCONUT HYBRIDIZATION PROJECT-CFIDP</p>
                                                    @php
                                                        $currentCategory = $activeCategory;
                                                    @endphp
                                                    @if($currentCategory === 'monthly_harvest')
                                                        <p class="uppercase text-xs mt-0.5">ON-FARM HYBRID SEEDNUT PRODUCTION</p>
                                                    @elseif($currentCategory === 'pollen_production')
                                                        <p class="uppercase text-xs mt-0.5">POLLEN PRODUCTION AND PROCESSING</p>
                                                    @elseif($currentCategory === 'hybrid_distribution')
                                                        <p class="uppercase text-xs mt-0.5">DISTRIBUTION OF HYBRID SEEDLINGS</p>
                                                    @elseif($currentCategory === 'nursery_operation')
                                                        <p class="uppercase text-xs mt-0.5">MONTHLY NURSERY OPERATION REPORT</p>
                                                    @elseif($currentCategory === 'terminal_report')
                                                        <p class="uppercase text-xs mt-0.5">TERMINAL REPORT FOR NURSERY OPERATIONS</p>
                                                    @endif
                                                    @php
                                                        $asOfDate = \Carbon\Carbon::create($data['year'], $data['month'] ?: 1, 1);
                                                        $isCumulative = ($data['export_range'] ?? 'single') === 'cumulative';
                                                        if ($data['year'] && empty($data['month'])) {
                                                            $asOfStr = in_array($currentCategory, ['hybrid_distribution', 'nursery_operation', 'terminal_report']) ? 'as of end of ' . $data['year'] : 'For the year ' . $data['year'];
                                                        } elseif ($isCumulative) {
                                                            $asOfStr = in_array($currentCategory, ['hybrid_distribution', 'nursery_operation', 'terminal_report']) ? 'Cumulative as of ' . $asOfDate->endOfMonth()->format('F d, Y') : 'For the months of January to ' . $asOfDate->format('F Y');
                                                        } else {
                                                            $asOfStr = in_array($currentCategory, ['hybrid_distribution', 'nursery_operation', 'terminal_report']) ? 'as of ' . $asOfDate->endOfMonth()->format('F d, Y') : 'For the month of ' . $asOfDate->format('F Y');
                                                        }
                                                    @endphp
                                                    <p class="text-xs font-semibold underline mt-1">{{ $asOfStr }}</p>
                                                </div>
                                            </div>

                                            {{-- Dynamic Table --}}
                                            <div class="mb-4 w-full overflow-x-auto" style="margin-top: 50px !important;">
                                                @if($currentCategory === 'monthly_harvest')
                                                    @include('filament.pages.partials.reports.monthly_harvest', ['reportData' => $pageSiteRecords, 'reportFarms' => $pageReportFarms])
                                                @elseif($currentCategory === 'pollen_production')
                                                    @include('filament.pages.partials.reports.pollen_production', ['reportData' => $pageSiteRecords])
                                                @elseif($currentCategory === 'hybrid_distribution')
                                                    @include('filament.pages.partials.reports.hybrid_distribution', ['reportData' => $pageSiteRecords])
                                                @elseif(in_array($currentCategory, ['nursery_operation', 'terminal_report']))
                                                    @include('filament.pages.partials.reports.nursery_operation', ['reportData' => $pageSiteRecords])
                                                @endif
                                            </div>

                                            {{-- Signatories --}}
                                            @php
                                                $site = $pageSiteRecords->first()->fieldSite ?? null;
                                                $selectedMonth = $data['month'] ?? null;
                                                $selectedYear = $data['year'] ?? null;
                                                $currentMonthRecords = ($selectedMonth && $currentCategory !== 'terminal_report')
                                                    ? $pageSiteRecords->filter(fn($r) => \Carbon\Carbon::parse($r->report_month)->month == $selectedMonth && \Carbon\Carbon::parse($r->report_month)->year == $selectedYear)
                                                    : $pageSiteRecords;
                                                $statusOrder = ['draft' => 0, 'prepared' => 1, 'reviewed' => 2, 'noted' => 3];
                                                $minStatus = $currentMonthRecords->isNotEmpty() ? $currentMonthRecords->min(fn($r) => $statusOrder[$r->status] ?? 0) : 0;
                                                $refRecord = $currentMonthRecords->first();
                                                $showPrepared = $minStatus >= 1; $showReviewed = $minStatus >= 2; $showNoted = $minStatus >= 3;
                                                $prepUser = $showPrepared ? $refRecord?->preparedByUser : null;
                                                $revUser = $showReviewed ? $refRecord?->reviewedByUser : null;
                                                $notedUser = $showNoted ? $refRecord?->notedByUser : null;
                                                $prepName = $showPrepared ? strtoupper($site->prepared_by_name ?? $prepUser?->name ?? '_______________________') : '_______________________';
                                                $prepTitle = $site->prepared_by_title ?? $prepUser?->role_title ?? 'COS/Agriculturist';
                                                $prepSig = $showPrepared && $prepUser?->signature_image ? \App\Helpers\SignatureHelper::getSignatureUrl($prepUser->signature_image) : null;
                                                $revName = $showReviewed ? strtoupper($site->reviewed_by_name ?? $revUser?->name ?? '_______________________') : '_______________________';
                                                $revTitle = $site->reviewed_by_title ?? $revUser?->role_title ?? 'Senior Agriculturist';
                                                $revSig = $showReviewed && $revUser?->signature_image ? \App\Helpers\SignatureHelper::getSignatureUrl($revUser->signature_image) : null;
                                                $notedName = $showNoted ? strtoupper($site->noted_by_name ?? $notedUser?->name ?? '_______________________') : '_______________________';
                                                $notedTitle = $site->noted_by_title ?? $notedUser?->role_title ?? 'PCDM/Division Chief I';
                                                $notedSig = $showNoted && $notedUser?->signature_image ? \App\Helpers\SignatureHelper::getSignatureUrl($notedUser->signature_image) : null;
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
                                                            @elseif($showPrepared && $prepUser?->signature_image)
                                                                <span style="font-style: italic; font-size: 10px; color: #666; margin-bottom: 4px;">Digitally Signed</span>
                                                            @endif
                                                            <p class="font-bold uppercase">{{ $prepName }}</p>
                                                            <div class="border-t border-black mt-1 pt-1 w-full">{{ $prepTitle }}</div>
                                                        </div>
                                                    </td>
                                                    <td class="px-8 align-bottom" style="height: 80px;">
                                                        <div class="flex flex-col items-center justify-end h-full">
                                                            @if($revSig)
                                                                <img src="{{ $revSig }}" style="height: 50px; object-fit: contain; margin-bottom: -8px;" alt="Signature">
                                                            @elseif($showReviewed && $revUser?->signature_image)
                                                                <span style="font-style: italic; font-size: 10px; color: #666; margin-bottom: 4px;">Digitally Signed</span>
                                                            @endif
                                                            <p class="font-bold uppercase">{{ $revName }}</p>
                                                            <div class="border-t border-black mt-1 pt-1 w-full">{{ $revTitle }}</div>
                                                        </div>
                                                    </td>
                                                    <td class="px-8 align-bottom" style="height: 80px;">
                                                        <div class="flex flex-col items-center justify-end h-full">
                                                            @if($notedSig)
                                                                <img src="{{ $notedSig }}" style="height: 50px; object-fit: contain; margin-bottom: -8px;" alt="Signature">
                                                            @elseif($showNoted && $notedUser?->signature_image)
                                                                <span style="font-style: italic; font-size: 10px; color: #666; margin-bottom: 4px;">Digitally Signed</span>
                                                            @endif
                                                            <p class="font-bold uppercase">{{ $notedName }}</p>
                                                            <div class="border-t border-black mt-1 pt-1 w-full">{{ $notedTitle }}</div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                            </div>
                                            <div class="w-5 shrink-0 no-print"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>


            </div>
        </div>
        @endteleport

        {{-- Cleanup class when modal closes --}}
        <script>
            document.addEventListener('livewire:navigated', () => document.documentElement.classList.remove('modal-open'));
        </script>
    @else
        <script>document.documentElement.classList.remove('modal-open');</script>
    @endif

    {{-- Global Print Script - kept outside conditional so Livewire doesn't strip it --}}
    <script>
        window.printAllPages = function() {
            const loader = document.getElementById('js-print-loader');
            if (loader) loader.style.display = 'flex';

            const pages = document.querySelectorAll('.report-site-page .report-page-container');
            if (!pages.length) {
                if (loader) loader.style.display = 'none';
                return;
            }

            let pagesHtml = '';
            pages.forEach((page, idx) => {
                const pageBreak = idx > 0 ? 'page-break-before: always;' : '';
                pagesHtml += '<div style="' + pageBreak + ' padding: 0.25in;">' + page.innerHTML + '</div>';
            });

            let iframe = document.getElementById('report-print-iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'report-print-iframe';
                // Must not be display: none or print() will silently fail in some browsers
                iframe.style.position = 'absolute';
                iframe.style.width = '0px';
                iframe.style.height = '0px';
                iframe.style.border = 'none';
                iframe.style.visibility = 'hidden';
                iframe.style.zIndex = '-1';
                document.body.appendChild(iframe);
            }

            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write('<!DOCTYPE html><ht' + 'ml><he' + 'ad><title>Report Print</title></he' + 'ad><bo' + 'dy></bo' + 'dy></ht' + 'ml>');
            doc.close();

            // Clone all Tailwind/Filament stylesheets from the parent window to ensure 100% identical output
            Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).forEach(node => {
                doc.head.appendChild(node.cloneNode(true));
            });

            // Add minimal print-specific overrides
            const style = doc.createElement('style');
            style.textContent = `
                @page { size: legal landscape; margin: 0; }
                * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
                html, body { 
                    background: white !important; 
                    color: black !important;
                    margin: 0; 
                    padding: 0; 
                }
                
                /* Hide any global background pseudo-elements (like the coconut farm bg) */
                body::before, body::after {
                    display: none !important;
                    content: none !important;
                    background: none !important;
                }
                
                /* Ensure tables maintain their exact visual structure from the overview */
                .report-page-container { 
                    background-color: white !important; 
                    color: black !important; 
                    box-shadow: none !important; 
                    border: none !important; 
                }
                
                /* Force black borders for printing clarity */
                table:not(.signature-table) th, 
                table:not(.signature-table) td { 
                    border-color: black !important; 
                }
                
                .no-print { display: none !important; }
            `;
            doc.head.appendChild(style);
            
            // Inject the HTML
            doc.body.innerHTML = pagesHtml;

            // Wait a bit longer (1200ms) to ensure the cloned CSS files are fully downloaded before printing
            setTimeout(() => {
                if (loader) loader.style.display = 'none';
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 1200);
        }
    </script>


</x-filament-panels::page>
