<?php

use Illuminate\Support\Facades\Route;
use App\Models\FieldSite;

Route::get('/', function () {
    $year = (int) request('year', now()->year);

    $sites = FieldSite::with([])->get()->map(function ($site) use ($year) {
        return [
            'name'          => $site->name,
            'harvests'      => \App\Models\MonthlyHarvest::where('field_site_id', $site->id)->whereYear('report_month', $year)->count(),
            'pollen'        => \App\Models\PollenProduction::where('field_site_id', $site->id)->whereYear('report_month', $year)->count(),
            'nursery'       => \App\Models\NurseryOperation::where('field_site_id', $site->id)->where('report_type', 'operation')->whereYear('report_month', $year)->count(),
            'distribution'  => \App\Models\HybridDistribution::where('field_site_id', $site->id)->whereYear('report_month', $year)->count(),
            'seednuts'      => (int) \App\Models\HarvestVariety::whereHas('monthlyHarvest', fn($q) => $q->withoutGlobalScopes()->where('field_site_id', $site->id)->whereYear('report_month', $year))->sum('seednuts_count'),
            'seedlings'     => (int) \App\Models\HybridDistribution::where('field_site_id', $site->id)->whereYear('report_month', $year)->sum('seedlings_planted'),
        ];
    });

    $totalHarvests      = (int) $sites->sum('harvests');
    $totalPollen        = (int) $sites->sum('pollen');
    $totalDistribution  = (int) $sites->sum('distribution');
    $totalSeednuts      = (int) $sites->sum('seednuts');
    $totalSeedlings     = (int) $sites->sum('seedlings');
    $siteCount          = FieldSite::count();

    // ── Seedling Distribution Section ──────────────────────────────
    $distMonth = (int) request('dist_month', now()->month);
    $distYear  = (int) request('dist_year', now()->year);
    $distMode  = request('dist_mode', 'monthly'); // 'monthly' or 'cumulative'
    $isCumulative = $distMode === 'cumulative';

    $allSites = FieldSite::all();
    $selectedDate = sprintf('%04d-%02d-01', $distYear, $distMonth);
    $selectedDateEnd = \Carbon\Carbon::create($distYear, $distMonth, 1)->endOfMonth()->format('Y-m-d');

    if ($isCumulative) {
        // ── CUMULATIVE MODE ──────────────────────────────────────
        $isNurseryCarried = false;
        $nurseryTargetMonth = \Carbon\Carbon::create($distYear, $distMonth, 1);

        // Available: all nursery stock up to selected month
        $availableBySite = $allSites->mapWithKeys(function ($site) use ($selectedDate) {
            $readyToPlant = \App\Models\NurseryBatchVariety::whereHas('batch.nurseryOperation', fn($q) =>
                $q->withoutGlobalScopes()
                    ->where('field_site_id', $site->id)
                    ->where('report_month', '<=', $selectedDate)
            )->sum('ready_to_plant');

            $dispatched = \App\Models\NurseryBatchVariety::whereHas('batch.nurseryOperation', fn($q) =>
                $q->withoutGlobalScopes()
                    ->where('field_site_id', $site->id)
                    ->where('report_month', '<=', $selectedDate)
            )->sum('seedlings_dispatched');

            $varieties = \App\Models\NurseryBatchVariety::whereHas('batch.nurseryOperation', fn($q) =>
                $q->withoutGlobalScopes()
                    ->where('field_site_id', $site->id)
                    ->where('report_month', '<=', $selectedDate)
            )->pluck('variety')->unique()->filter()->implode(', ');

            return [$site->id => [
                'available' => max(0, (int) $readyToPlant - (int) $dispatched),
                'varieties' => $varieties,
            ]];
        });

        $totalAvailable = $availableBySite->sum('available');

        // Distributed: all distribution records up to selected month
        $distThisMonth = $allSites->mapWithKeys(function ($site) use ($selectedDate) {
            $records = \App\Models\HybridDistribution::where('field_site_id', $site->id)
                ->where('report_month', '<=', $selectedDate)
                ->get();
            return [$site->id => [
                'distributed' => (int) $records->sum('seedlings_planted'),
                'farmers'     => $records->count(),
                'varieties'   => $records->pluck('variety')->filter()->unique()->implode(', '),
            ]];
        });

        $totalDistributed = $distThisMonth->sum('distributed');
        $totalFarmers     = $distThisMonth->sum('farmers');
        $totalRemaining   = max(0, $totalAvailable - $totalDistributed);

        // Variety breakdown: cumulative
        $varietyBreakdown = \App\Models\NurseryBatchVariety::whereHas('batch.nurseryOperation', fn($q) =>
            $q->withoutGlobalScopes()->where('report_month', '<=', $selectedDate)
        )->get()
        ->groupBy('variety')
        ->map(function ($rows, $variety) use ($selectedDate) {
            $sown       = (int) $rows->sum('seednuts_sown');
            $ready      = (int) $rows->sum('ready_to_plant');
            $dispatched = (int) $rows->sum('seedlings_dispatched');
            $available  = max(0, $ready - $dispatched);

            // Match distribution records - variety names may differ
            // Nursery: 'Catigan Green Dwarf × TALL', Distribution: 'Catigan Green Dwarf'
            $baseVariety = trim(preg_replace('/\s*[×x]\s*.*/i', '', $variety));
            $distributed = (int) \App\Models\HybridDistribution::withoutGlobalScopes()
                ->where('report_month', '<=', $selectedDate)
                ->where(function ($q) use ($variety, $baseVariety) {
                    $q->where('variety', $variety)
                      ->orWhere('variety', $baseVariety)
                      ->orWhere('variety', 'LIKE', $baseVariety . '%');
                })
                ->sum('seedlings_planted');

            $remaining = max(0, $available - $distributed);

            return [
                'variety'     => $variety ?: 'Unknown',
                'sown'        => $sown,
                'available'   => $available,
                'distributed' => $distributed,
                'remaining'   => $remaining,
            ];
        })->filter(fn($v) => $v['sown'] > 0 || $v['available'] > 0 || $v['distributed'] > 0)
        ->values();

        // Cumulative card value equals totalAvailable in this mode
        $cumulativeAvailable = $totalAvailable;

    } else {
        // ── MONTHLY MODE (default) ───────────────────────────────

        // 1. Available stock: carry forward from latest recorded nursery month
        $latestMonthRecorded = \App\Models\NurseryOperation::withoutGlobalScopes()
            ->where('report_month', '<=', $selectedDate)
            ->whereHas('batches.varieties', fn($q) => $q->where('ready_to_plant', '>', 0))
            ->orderBy('report_month', 'desc')
            ->value('report_month');

        $nurseryTargetMonth = $latestMonthRecorded ? \Carbon\Carbon::parse($latestMonthRecorded) : \Carbon\Carbon::create($distYear, $distMonth, 1);
        $isNurseryCarried = $latestMonthRecorded && ($nurseryTargetMonth->month != $distMonth || $nurseryTargetMonth->year != $distYear);

        $availableBySite = $allSites->mapWithKeys(function ($site) use ($nurseryTargetMonth) {
            $readyToPlant = \App\Models\NurseryBatchVariety::whereHas('batch.nurseryOperation', fn($q) =>
                $q->withoutGlobalScopes()
                    ->where('field_site_id', $site->id)
                    ->whereYear('report_month', $nurseryTargetMonth->year)
                    ->whereMonth('report_month', $nurseryTargetMonth->month)
            )->sum('ready_to_plant');

            $dispatched = \App\Models\NurseryBatchVariety::whereHas('batch.nurseryOperation', fn($q) =>
                $q->withoutGlobalScopes()
                    ->where('field_site_id', $site->id)
                    ->whereYear('report_month', $nurseryTargetMonth->year)
                    ->whereMonth('report_month', $nurseryTargetMonth->month)
            )->sum('seedlings_dispatched');

            $varieties = \App\Models\NurseryBatchVariety::whereHas('batch.nurseryOperation', fn($q) =>
                $q->withoutGlobalScopes()
                    ->where('field_site_id', $site->id)
                    ->whereYear('report_month', $nurseryTargetMonth->year)
                    ->whereMonth('report_month', $nurseryTargetMonth->month)
            )->pluck('variety')->unique()->filter()->implode(', ');

            return [$site->id => [
                'available' => max(0, (int) $readyToPlant - (int) $dispatched),
                'varieties' => $varieties,
            ]];
        });

        $totalAvailable = $availableBySite->sum('available');

        // 2. Distributed this month
        $distThisMonth = $allSites->mapWithKeys(function ($site) use ($distYear, $distMonth) {
            $records = \App\Models\HybridDistribution::where('field_site_id', $site->id)
                ->whereYear('report_month', $distYear)
                ->whereMonth('report_month', $distMonth)
                ->get();
            return [$site->id => [
                'distributed' => (int) $records->sum('seedlings_planted'),
                'farmers'     => $records->count(),
                'varieties'   => $records->pluck('variety')->filter()->unique()->implode(', '),
            ]];
        });

        $totalDistributed = $distThisMonth->sum('distributed');
        $totalFarmers     = $distThisMonth->sum('farmers');
        $totalRemaining   = max(0, $totalAvailable - $totalDistributed);

        // 4. Variety breakdown for the selected month
        $varietyBreakdown = \App\Models\NurseryBatchVariety::whereHas('batch.nurseryOperation', fn($q) =>
            $q->withoutGlobalScopes()
                ->whereYear('report_month', $nurseryTargetMonth->year)
                ->whereMonth('report_month', $nurseryTargetMonth->month)
        )->get()
        ->groupBy('variety')
        ->map(function ($rows, $variety) use ($distYear, $distMonth) {
            $sown       = (int) $rows->sum('seednuts_sown');
            $ready      = (int) $rows->sum('ready_to_plant');
            $dispatched = (int) $rows->sum('seedlings_dispatched');
            $available  = max(0, $ready - $dispatched);

            // Match distribution records - variety names may differ
            // Nursery: 'Catigan Green Dwarf × TALL', Distribution: 'Catigan Green Dwarf'
            $baseVariety = trim(preg_replace('/\s*[×x]\s*.*/i', '', $variety));
            $distributed = (int) \App\Models\HybridDistribution::withoutGlobalScopes()
                ->whereYear('report_month', $distYear)
                ->whereMonth('report_month', $distMonth)
                ->where(function ($q) use ($variety, $baseVariety) {
                    $q->where('variety', $variety)
                      ->orWhere('variety', $baseVariety)
                      ->orWhere('variety', 'LIKE', $baseVariety . '%');
                })
                ->sum('seedlings_planted');

            $remaining = max(0, $available - $distributed);

            return [
                'variety'     => $variety ?: 'Unknown',
                'sown'        => $sown,
                'available'   => $available,
                'distributed' => $distributed,
                'remaining'   => $remaining,
            ];
        })->filter(fn($v) => $v['sown'] > 0 || $v['available'] > 0 || $v['distributed'] > 0)
        ->values();

        // 5. Cumulative nursery availability (all months up to selected)
        $cumulativeReady = (int) \App\Models\NurseryBatchVariety::whereHas('batch.nurseryOperation', fn($q) =>
            $q->withoutGlobalScopes()->where('report_month', '<=', $selectedDate)
        )->sum('ready_to_plant');

        $cumulativeDispatched = (int) \App\Models\NurseryBatchVariety::whereHas('batch.nurseryOperation', fn($q) =>
            $q->withoutGlobalScopes()->where('report_month', '<=', $selectedDate)
        )->sum('seedlings_dispatched');

        $cumulativeAvailable = max(0, $cumulativeReady - $cumulativeDispatched);
    }

    // 6. Merge per-site data for display
    $distSiteData = $allSites->map(function ($site) use ($availableBySite, $distThisMonth) {
        $avail = $availableBySite[$site->id] ?? ['available' => 0, 'varieties' => ''];
        $dist = $distThisMonth[$site->id] ?? ['distributed' => 0, 'farmers' => 0, 'varieties' => ''];
        
        $varieties = $dist['varieties'] ?: $avail['varieties'];
        $remaining = max(0, $avail['available'] - $dist['distributed']);

        return [
            'name'        => $site->name,
            'available'   => $avail['available'],
            'distributed' => $dist['distributed'],
            'remaining'   => $remaining,
            'farmers'     => $dist['farmers'],
            'varieties'   => $varieties,
        ];
    })->filter(fn($s) => $s['available'] > 0 || $s['distributed'] > 0 || $s['farmers'] > 0);

    return view('welcome', compact(
        'sites', 'year', 'siteCount',
        'totalHarvests', 'totalPollen', 'totalDistribution',
        'totalSeednuts', 'totalSeedlings',
        'distMonth', 'distYear', 'distMode', 'isCumulative',
        'totalAvailable', 'totalDistributed', 'totalFarmers',
        'totalRemaining', 'varietyBreakdown', 'cumulativeAvailable',
        'distSiteData',
        'nurseryTargetMonth', 'isNurseryCarried'
    ));
});

// ─── QR Code Routes ─────────────────────────────────────────────
// Quick-add: scanned QR redirects to Create Monthly Harvest with site pre-filled
Route::get('/site/{fieldSite}/quick-add', function (FieldSite $fieldSite) {
    return redirect()->to(
        '/portal/monthly-harvests/create?field_site_id=' . $fieldSite->id
    );
})->middleware(['auth'])->name('site.quick-add');

// Printable QR code page (for printing & sticking on field markers)
Route::get('/site/{fieldSite}/qr', function (FieldSite $fieldSite) {
    $quickAddUrl = url("/site/{$fieldSite->id}/quick-add");
    return view('qr-code-print', [
        'site' => $fieldSite,
        'qrUrl' => $quickAddUrl,
    ]);
})->middleware(['auth'])->name('site.qr');

// Legacy redirect to prevent broken bookmarks
Route::any('/admin/{any?}', function ($any = null) {
    $query = request()->getQueryString();
    $target = '/portal' . ($any ? '/' . $any : '');
    if ($query) {
        $target .= '?' . $query;
    }
    return redirect()->to($target, 301);
})->where('any', '.*');

// Pending Approval Page
Route::get('/pending-approval', function () {
    return view('pending-approval');
})->name('pending.approval');

// Legal Pages
Route::view('/privacy-policy', 'privacy')->name('privacy');
Route::view('/terms-of-service', 'terms')->name('terms');
