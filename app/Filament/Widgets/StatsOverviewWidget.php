<?php

namespace App\Filament\Widgets;

use App\Models\HybridDistribution;
use App\Models\HybridizationRecord;
use App\Models\MonthlyHarvest;
use App\Models\NurseryOperation;
use App\Models\PollenProduction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public ?int $year = null;

    public function mount(): void
    {
        $this->year = (int) \Illuminate\Support\Facades\Session::get('global_dashboard_year', now()->year);
    }

    #[On('dashboard-year-changed')]
    public function onYearChanged(int $year): void
    {
        $this->year = $year;
    }

    public static function canView(): bool
    {
        // sub supervisor allowed
        return !auth()->user()?->isSuperAdmin();
    }

    public function getHeading(): ?string
    {
        $user = auth()->user();
        $y = $this->year ?? now()->year;
        if ($user?->isManager() || $user?->isAdmin()) {
            return "Field Data Stats — {$y}";
        }
        return "Field Data Stats — {$y}";
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $isSupervisor = $user?->isSupervisor() || $user?->isSubSupervisor();
        $siteId = $user?->field_site_id;
        $year = $this->year ?? (int) now()->year;

        // Build queries with year and optional site scoping
        $harvestQuery = MonthlyHarvest::query()->whereYear('report_month', $year);
        $nurseryQuery = NurseryOperation::query()->where('report_type', 'operation')->whereYear('report_month', $year);
        $distributionQuery = HybridDistribution::query()->whereYear('report_month', $year);
        $terminalQuery = NurseryOperation::query()->where('report_type', 'terminal')->whereYear('report_month', $year);
        $pollenQuery = PollenProduction::query()->whereYear('report_month', $year);
        $recordQuery = HybridizationRecord::query();
        $readyQuery = HybridizationRecord::query()->readyForHarvest();

        if ($isSupervisor && $siteId) {
            $distributionQuery->where('field_site_id', $siteId);
            $harvestQuery->where('field_site_id', $siteId);
            $nurseryQuery->where('field_site_id', $siteId);
            $terminalQuery->where('field_site_id', $siteId);
            $pollenQuery->where('field_site_id', $siteId);
            $recordQuery->where('field_site_id', $siteId);
            $readyQuery->where('field_site_id', $siteId);
        }

        $readyCount = $readyQuery->count();

        $stats = [];

        // Base record usually accessible if they have access to the dashboard
        $stats[] = Stat::make('Ready for Harvest', $readyCount)
            ->description($readyCount > 0 ? 'Need attention now' : 'All on track')
            ->icon('heroicon-o-calendar-days')
            ->color($readyCount > 0 ? 'danger' : 'success')
            ->extraAttributes(['class' => 'stat-gradient-6']);

        if (\App\Filament\Resources\HybridDistributionResource::canViewAny()) {
            $stats[] = Stat::make('Hybrid Dist.', $distributionQuery->count())
                ->description('Farmer distribution records')
                ->icon('heroicon-o-truck')
                ->extraAttributes(['class' => 'stat-gradient-1']);
        }

        if (\App\Filament\Resources\MonthlyHarvestResource::canViewAny()) {
            $stats[] = Stat::make('Harvest', $harvestQuery->count())
                ->description('Seednut production records')
                ->icon('heroicon-o-academic-cap')
                ->extraAttributes(['class' => 'stat-gradient-2']);
        }

        if (\App\Filament\Resources\NurseryOperationResource::canViewAny()) {
            $stats[] = Stat::make('Nursery', $nurseryQuery->count())
                ->description('Nursery reports')
                ->icon('heroicon-o-sun')
                ->extraAttributes(['class' => 'stat-gradient-3']);
        }

        if (\App\Filament\Resources\TerminalResource::canViewAny()) {
            $stats[] = Stat::make('Terminal Rep.', $terminalQuery->count())
                ->description('Terminal reports')
                ->icon('heroicon-o-clipboard-document-check')
                ->extraAttributes(['class' => 'stat-gradient-4']);
        }

        if (\App\Filament\Resources\PollenProductionResource::canViewAny()) {
            $stats[] = Stat::make('Pollen', $pollenQuery->count())
                ->description('Pollen production')
                ->icon('heroicon-o-beaker')
                ->extraAttributes(['class' => 'stat-gradient-5']);
        }

        return $stats;
    }
}
