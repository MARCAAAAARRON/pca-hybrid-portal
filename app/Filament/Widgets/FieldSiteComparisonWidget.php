<?php

namespace App\Filament\Widgets;

use App\Models\FieldSite;
use App\Models\HybridDistribution;
use App\Models\MonthlyHarvest;
use App\Models\NurseryOperation;
use App\Models\PollenProduction;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class FieldSiteComparisonWidget extends Widget
{
    protected static string $view = 'filament.widgets.field-site-comparison';

    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    /**
     * Hidden from all dashboards — this feature is still in beta.
     */
    public static function canView(): bool
    {
        return false;
    }


    // Period A filters
    public ?int $monthA = null;
    public ?int $yearA = null;

    // Period B filters
    public ?int $monthB = null;
    public ?int $yearB = null;

    public function mount(): void
    {
        $globalYear = (int) \Illuminate\Support\Facades\Session::get('global_dashboard_year', now()->year);

        // Default: Period A = previous month, Period B = current month
        $now = now();
        $prev = $now->copy()->subMonth();

        $this->monthA = $prev->month;
        $this->yearA = $prev->year;
        $this->monthB = $now->month;
        $this->yearB = $globalYear;
    }

    #[On('dashboard-year-changed')]
    public function onYearChanged(int $year): void
    {
        $this->yearB = $year;
    }

    public static function canView(): bool
    {
        if (auth()->user()?->isSubSupervisor()) return false;
        return ! auth()->user()?->isSuperAdmin();
    }

    /**
     * Count records for a specific model in a given month+year, optionally scoped to a site.
     */
    private function countForPeriod(string $modelClass, int $month, int $year, ?int $siteId = null, ?string $reportType = null): int
    {
        $query = $modelClass::query()
            ->whereYear('report_month', $year)
            ->whereMonth('report_month', $month);

        if ($reportType !== null) {
            $query->where('report_type', $reportType);
        }

        if ($siteId !== null) {
            $query->where('field_site_id', $siteId);
        }

        return $query->count();
    }

    /**
     * Build the counts array for a single site in a given period.
     */
    private function buildPeriodData(int $month, int $year, ?int $siteId = null): array
    {
        $harvest      = $this->countForPeriod(MonthlyHarvest::class, $month, $year, $siteId);
        $nursery      = $this->countForPeriod(NurseryOperation::class, $month, $year, $siteId, 'operation');
        $pollen       = $this->countForPeriod(PollenProduction::class, $month, $year, $siteId);
        $distribution = $this->countForPeriod(HybridDistribution::class, $month, $year, $siteId);
        $terminal     = $this->countForPeriod(NurseryOperation::class, $month, $year, $siteId, 'terminal');

        return [
            'harvest'      => $harvest,
            'nursery'      => $nursery,
            'pollen'       => $pollen,
            'distribution' => $distribution,
            'terminal'     => $terminal,
            'total'        => $harvest + $nursery + $pollen + $distribution + $terminal,
        ];
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $isSupervisor = $user?->isSupervisor();
        $siteId = $user?->field_site_id;

        $monthA = $this->monthA ?? now()->subMonth()->month;
        $yearA  = $this->yearA ?? now()->subMonth()->year;
        $monthB = $this->monthB ?? now()->month;
        $yearB  = $this->yearB ?? now()->year;

        // Determine which sites to show
        if ($isSupervisor && $siteId) {
            $sites = FieldSite::where('id', $siteId)->get();
        } else {
            $sites = FieldSite::all();
        }

        $categories = ['harvest', 'nursery', 'pollen', 'distribution', 'terminal'];
        $categoryLabels = [
            'harvest'      => 'Harvest',
            'nursery'      => 'Nursery',
            'pollen'       => 'Pollen',
            'distribution' => 'Distribution',
            'terminal'     => 'Terminal',
        ];

        // ── Per-Site Chart Data (grouped bar: Period A vs B totals per site) ──
        $siteLabels = [];
        $siteDataA  = [];
        $siteDataB  = [];

        foreach ($sites as $site) {
            $periodA = $this->buildPeriodData($monthA, $yearA, $site->id);
            $periodB = $this->buildPeriodData($monthB, $yearB, $site->id);
            $siteLabels[] = $site->name;
            $siteDataA[]  = $periodA['total'];
            $siteDataB[]  = $periodB['total'];
        }

        // ── Per-Category Chart Data (grouped bar: Period A vs B per data category, aggregated across sites) ──
        $catDataA = [];
        $catDataB = [];

        foreach ($categories as $cat) {
            $sumA = 0;
            $sumB = 0;
            foreach ($sites as $site) {
                $reportType = match ($cat) {
                    'nursery'  => 'operation',
                    'terminal' => 'terminal',
                    default    => null,
                };
                $modelClass = match ($cat) {
                    'harvest'      => MonthlyHarvest::class,
                    'nursery'      => NurseryOperation::class,
                    'pollen'       => PollenProduction::class,
                    'distribution' => HybridDistribution::class,
                    'terminal'     => NurseryOperation::class,
                };
                $sumA += $this->countForPeriod($modelClass, $monthA, $yearA, $site->id, $reportType);
                $sumB += $this->countForPeriod($modelClass, $monthB, $yearB, $site->id, $reportType);
            }
            $catDataA[] = $sumA;
            $catDataB[] = $sumB;
        }

        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        $yearOptions = collect(range(now()->year, 2024, -1))
            ->mapWithKeys(fn ($y) => [$y => $y])
            ->toArray();

        $labelA = $months[$monthA] . ' ' . $yearA;
        $labelB = $months[$monthB] . ' ' . $yearB;

        return [
            'months'         => $months,
            'yearOptions'    => $yearOptions,
            'labelA'         => $labelA,
            'labelB'         => $labelB,
            // Per-site chart
            'siteLabels'     => $siteLabels,
            'siteDataA'      => $siteDataA,
            'siteDataB'      => $siteDataB,
            // Per-category chart
            'catLabels'      => array_values($categoryLabels),
            'catDataA'       => $catDataA,
            'catDataB'       => $catDataB,
        ];
    }
}
