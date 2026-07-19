<?php

namespace App\Filament\Widgets;

use App\Models\HarvestVariety;
use App\Models\HybridDistribution;
use App\Models\PollenProduction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class EfficiencyStatsWidget extends BaseWidget
{
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';

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
        return "Efficiency Metrics — {$y}";
    }

    protected function getStats(): array
    {
        $year = $this->year ?? (int) now()->year;

        $totalPollen = PollenProduction::withoutGlobalScopes()->whereYear('report_month', $year)->sum(\DB::raw('CAST("total_utilization" AS NUMERIC)'));
        $totalSeednuts = HarvestVariety::whereHas('monthlyHarvest', function ($q) use ($year) {
            $q->withoutGlobalScopes()->whereYear('report_month', $year);
        })->sum('seednuts_count');
        $totalSeedlings = HybridDistribution::withoutGlobalScopes()->whereYear('report_month', $year)->sum('seedlings_planted');

        $pollenEfficiency = $totalPollen > 0 ? round($totalSeednuts / $totalPollen, 1) : 0;
        $survivalRate = $totalSeednuts > 0 ? round(($totalSeedlings / $totalSeednuts) * 100, 1) : 0;

        return [
            Stat::make('Pollen Efficiency', $pollenEfficiency)
                ->description('Seednuts produced per gram of pollen')
                ->descriptionIcon('heroicon-m-bolt')
                ->color($pollenEfficiency >= 10 ? 'success' : 'warning')
                ->chart([7, 8, 9, 10, $pollenEfficiency]),

            Stat::make('Seedling Survival Rate', $survivalRate . '%')
                ->description('From harvest to distribution')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color($survivalRate >= 80 ? 'success' : ($survivalRate >= 50 ? 'warning' : 'danger'))
                ->chart([60, 70, 75, 80, $survivalRate]),
                
            Stat::make('Total Loss Pipeline', number_format($totalSeednuts - $totalSeedlings))
                ->description('Seednuts that didn\'t reach distribution')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
