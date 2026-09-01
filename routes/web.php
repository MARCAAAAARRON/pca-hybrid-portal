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

    $allSites = FieldSite::all();

    // 1. Available stock: if selected month has nursery records, use it; otherwise carry forward from latest recorded nursery month
    $selectedDate = sprintf('%04d-%02d-01', $distYear, $distMonth);

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

    // 2. Distributed this month (from HybridDistribution)
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

    // 3. Merge per-site data for display
    $distSiteData = $allSites->map(function ($site) use ($availableBySite, $distThisMonth) {
        $avail = $availableBySite[$site->id] ?? ['available' => 0, 'varieties' => ''];
        $dist = $distThisMonth[$site->id] ?? ['distributed' => 0, 'farmers' => 0, 'varieties' => ''];
        
        $varieties = $dist['varieties'] ?: $avail['varieties'];

        return [
            'name'        => $site->name,
            'available'   => $avail['available'],
            'distributed' => $dist['distributed'],
            'farmers'     => $dist['farmers'],
            'varieties'   => $varieties,
        ];
    })->filter(fn($s) => $s['available'] > 0 || $s['distributed'] > 0 || $s['farmers'] > 0);

    return view('welcome', compact(
        'sites', 'year', 'siteCount',
        'totalHarvests', 'totalPollen', 'totalDistribution',
        'totalSeednuts', 'totalSeedlings',
        'distMonth', 'distYear',
        'totalAvailable', 'totalDistributed', 'totalFarmers',
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
