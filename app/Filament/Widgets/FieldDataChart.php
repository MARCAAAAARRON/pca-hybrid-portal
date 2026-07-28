<?php

namespace App\Filament\Widgets;

use App\Models\HybridDistribution;
use App\Models\MonthlyHarvest;
use App\Models\NurseryOperation;
use App\Models\PollenProduction;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class FieldDataChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

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
        if (auth()->user()?->role === \'sub_supervisor\') return false;
        return !auth()->user()?->isSuperAdmin();
    }

    public function getHeading(): ?string
    {
        $user = auth()->user();
        $y = $this->year ?? now()->year;
        if ($user?->isManager() || $user?->isAdmin()) {
            return "Field Data Overview (Combined) — {$y}";
        }
        return "Field Data Overview — {$y}";
    }

    protected function getData(): array
    {
        $user = auth()->user();
        $isSupervisor = $user?->isSupervisor();
        $siteId = $user?->field_site_id;
        $year = $this->year ?? (int) now()->year;

        $harvestQuery = MonthlyHarvest::query()->whereYear('report_month', $year);
        $nurseryQuery = NurseryOperation::query()->where('report_type', 'operation')->whereYear('report_month', $year);
        $distributionQuery = HybridDistribution::query()->whereYear('report_month', $year);
        $terminalQuery = NurseryOperation::query()->where('report_type', 'terminal')->whereYear('report_month', $year);
        $pollenQuery = PollenProduction::query()->whereYear('report_month', $year);

        if ($isSupervisor && $siteId) {
            $harvestQuery->where('field_site_id', $siteId);
            $nurseryQuery->where('field_site_id', $siteId);
            $distributionQuery->where('field_site_id', $siteId);
            $terminalQuery->where('field_site_id', $siteId);
            $pollenQuery->where('field_site_id', $siteId);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Records',
                    'data' => [
                        $harvestQuery->count(),
                        $nurseryQuery->count(),
                        $distributionQuery->count(),
                        $terminalQuery->count(),
                        $pollenQuery->count(),
                    ],
                    'backgroundColor' => [
                        '#c8e6c9', '#bbdefb', '#e1bee7', '#fed7aa', '#ffecb3'
                    ],
                    'borderColor' => [
                        '#4caf50', '#2196f3', '#9c27b0', '#ff9800', '#f57f17'
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['Harvest', 'Nursery', 'Distribution', 'Terminal Report', 'Pollen'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected static ?string $maxHeight = '750px';

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }
}
