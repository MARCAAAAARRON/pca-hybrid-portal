<?php

namespace App\Filament\Widgets;

use App\Models\FieldSite;
use App\Models\HarvestVariety;
use App\Models\MonthlyHarvest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class SiteProductionRankingChart extends ChartWidget
{
    protected static ?int $sort = -7;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '400px';

    public ?int $year = null;

    public function mount(): void
    {
        $this->year = (int) now()->year;
    }

    #[On('dashboard-year-changed')]
    public function onYearChanged(int $year): void
    {
        $this->year = $year;
    }

    public static function canView(): bool
    {
        if (auth()->user()?->role === 'sub_supervisor') return false;
        return auth()->user()?->isManager() || auth()->user()?->isAdmin();
    }

    public function getHeading(): ?string
    {
        $y = $this->year ?? now()->year;
        return "🏆 Site Production Ranking — {$y}";
    }

    protected function getData(): array
    {
        $year = $this->year ?? (int) now()->year;
        $prevYear = $year - 1;
        $sites = FieldSite::all();

        $siteData = [];
        foreach ($sites as $site) {
            $totalCurrent = HarvestVariety::whereHas('monthlyHarvest', function ($q) use ($site, $year) {
                $q->withoutGlobalScopes()->where('field_site_id', $site->id)->whereYear('report_month', $year);
            })->sum('seednuts_count');

            $totalPrev = HarvestVariety::whereHas('monthlyHarvest', function ($q) use ($site, $prevYear) {
                $q->withoutGlobalScopes()->where('field_site_id', $site->id)->whereYear('report_month', $prevYear);
            })->sum('seednuts_count');

            $siteData[] = ['name' => $site->name, 'total_current' => $totalCurrent, 'total_prev' => $totalPrev];
        }

        usort($siteData, fn($a, $b) => $b['total_current'] - $a['total_current']);

        $labels = [];
        $dataCurrent = [];
        $dataPrev = [];

        foreach ($siteData as $i => $s) {
            $labels[] = $s['name'];
            $dataCurrent[] = $s['total_current'];
            $dataPrev[] = $s['total_prev'];
        }

        return [
            'datasets' => [
                [
                    'label' => "{$year} (Current)",
                    'data' => $dataCurrent,
                    'backgroundColor' => 'rgba(22, 163, 74, 0.7)',
                    'borderColor' => '#16a34a',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
                [
                    'label' => "{$prevYear} (Previous)",
                    'data' => $dataPrev,
                    'backgroundColor' => 'rgba(147, 51, 234, 0.7)',
                    'borderColor' => '#9333ea',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'maintainAspectRatio' => false,
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                    'grid' => ['display' => true, 'color' => 'rgba(0,0,0,0.05)'],
                ],
                'y' => ['grid' => ['display' => false]],
            ],
        ];
    }
}
