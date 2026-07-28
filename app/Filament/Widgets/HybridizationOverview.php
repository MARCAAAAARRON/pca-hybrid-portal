<?php

namespace App\Filament\Widgets;

use App\Models\HybridizationRecord;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HybridizationOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $isSupervisor = $user?->isSupervisor();
        $siteId = $user?->field_site_id;

        $query = HybridizationRecord::query();
        $readyQuery = HybridizationRecord::query()->readyForHarvest();

        if ($isSupervisor && $siteId) {
            $query->where('field_site_id', $siteId);
            $readyQuery->where('field_site_id', $siteId);
        }

        $total = $query->count();
        $ready = $readyQuery->count();
        $harvested = (clone $query)->where('growth_status', 'harvested')->count();
        $active = $total - $harvested;

        return [
            Stat::make('Total Records', $total)
                ->description('All recorded hybrid lines')
                ->icon('heroicon-o-beaker')
                ->color('info'),
            Stat::make('Ready for Harvest', $ready)
                ->description($ready > 0 ? 'Urgent attention required' : 'No overdue harvests')
                ->icon('heroicon-o-calendar-days')
                ->color($ready > 0 ? 'danger' : 'success')
                ->chart([7, 3, 5, 2, 4, 6, 8])
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
            Stat::make('Active Growing', $active)
                ->description('Seedlings still in progress')
                ->icon('heroicon-o-sun')
                ->color('warning'),
            Stat::make('Total Harvested', $harvested)
                ->description('Completed cycles')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->role !== 'sub_supervisor';
    }
}