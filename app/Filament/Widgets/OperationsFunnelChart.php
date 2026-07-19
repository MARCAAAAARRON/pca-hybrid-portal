<?php

namespace App\Filament\Widgets;

use App\Models\HarvestVariety;
use App\Models\HybridDistribution;
use App\Models\PollenProduction;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class OperationsFunnelChart extends ChartWidget
{
    protected static ?int $sort = 8;
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
        return auth()->user()?->isManager() || auth()->user()?->isAdmin();
    }

    public function getHeading(): ?string
    {
        $y = $this->year ?? now()->year;
        return "📊 Regional Operations Funnel — {$y}";
    }

    protected function getData(): array
    {
        $year = $this->year ?? (int) now()->year;

        $pollenUtilized = PollenProduction::withoutGlobalScopes()->whereYear('report_month', $year)->sum(\DB::raw('CAST("total_utilization" AS NUMERIC)'));
        $seednutsHarvested = HarvestVariety::whereHas('monthlyHarvest', function ($q) use ($year) {
            $q->withoutGlobalScopes()->whereYear('report_month', $year);
        })->sum('seednuts_count');
        $seedlingsDistributed = HybridDistribution::withoutGlobalScopes()->whereYear('report_month', $year)->sum('seedlings_planted');

        return [
            'datasets' => [
                [
                    'label' => 'Total Count',
                    'data' => [$pollenUtilized, $seednutsHarvested, $seedlingsDistributed],
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(22, 163, 74, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                    ],
                    'borderColor' => ['#f59e0b', '#16a34a', '#0ea5e9'],
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => ['Pollen Used (g)', 'Seednuts Harvested', 'Seedlings Distributed'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => ['callbacks' => []],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['display' => true, 'color' => 'rgba(0,0,0,0.05)'],
                ],
                'x' => ['grid' => ['display' => false]],
            ],
        ];
    }
}
