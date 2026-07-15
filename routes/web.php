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

    return view('welcome', compact(
        'sites', 'year', 'siteCount',
        'totalHarvests', 'totalPollen', 'totalDistribution',
        'totalSeednuts', 'totalSeedlings'
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
