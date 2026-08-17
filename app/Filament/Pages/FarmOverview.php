<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class FarmOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Farm Dashboard';
    protected static ?string $title = 'Farm Dashboard';
    protected static ?int $navigationSort = 1; // Right below Home (which is 0)

    protected static string $view = 'filament.pages.farm-overview';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        // Not for sub_supervisors or superadmins
        if ($user->isSubSupervisor() || $user->isSuperAdmin()) return false;
        return true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getColumns(): int | string | array
    {
        return 1;
    }

    /**
     * Explicitly list only farm/production overview widgets.
     */
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\AdminSummaryStats::class,
            \App\Filament\Widgets\StatsOverviewWidget::class,
            \App\Filament\Widgets\HybridizationOverview::class,
            \App\Filament\Widgets\SiteProductionRankingChart::class,
            \App\Filament\Widgets\FieldSiteAnalyticsChart::class,
            \App\Filament\Widgets\FieldDataChart::class,
            \App\Filament\Widgets\OperationsFunnelChart::class,
            \App\Filament\Widgets\EfficiencyStatsWidget::class,
            \App\Filament\Widgets\PerFarmBreakdown::class,
        ];
    }

    public function getVisibleWidgets(): array
    {
        return $this->filterVisibleWidgets($this->getWidgets());
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'A comprehensive overview of all farm production data, field site analytics, and operational efficiency metrics.';
    }
}
