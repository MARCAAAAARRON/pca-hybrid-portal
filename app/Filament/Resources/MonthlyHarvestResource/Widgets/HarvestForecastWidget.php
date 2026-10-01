<?php

namespace App\Filament\Resources\MonthlyHarvestResource\Widgets;

use App\Models\HybridizationRecord;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class HarvestForecastWidget extends Widget
{
    protected static string $view = 'filament.resources.monthly-harvest-resource.widgets.harvest-forecast-widget';

    protected int | string | array $columnSpan = 'full';

    /**
     * Build the forecast data: group upcoming unharvested records by estimated harvest month.
     */
    public function getForecastData(): array
    {
        $user = auth()->user();

        $query = HybridizationRecord::query()
            ->withoutGlobalScopes()
            ->with('fieldSite')
            ->where('growth_status', '!=', 'harvested')
            ->whereNotNull('date_planted');

        // Supervisors only see their own site
        if (($user?->isSupervisor() || $user?->isSubSupervisor()) && $user->field_site_id) {
            $query->where('field_site_id', $user->field_site_id);
        }

        $records = $query->get();

        // Group by estimated harvest month
        $grouped = $records
            ->filter(fn ($r) => $r->estimated_harvest_date !== null)
            ->groupBy(fn ($r) => $r->estimated_harvest_date->format('Y-m'))
            ->sortKeys();

        $months = [];
        $now = Carbon::now()->startOfDay();

        foreach ($grouped as $yearMonth => $items) {
            $date = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
            $isPast = $date->copy()->endOfMonth()->lt($now);
            $isCurrent = $date->format('Y-m') === $now->format('Y-m');

            $totalRecords = $items->count();

            // Count by growth status
            $statusBreakdown = $items->groupBy('growth_status')->map->count()->toArray();

            // Count by site
            $siteBreakdown = $items->groupBy(fn ($r) => $r->fieldSite?->name ?? 'Unknown')
                ->map->count()
                ->sortDesc()
                ->toArray();

            $months[] = [
                'label'       => $date->format('M Y'),
                'year_month'  => $yearMonth,
                'total'       => $totalRecords,
                'is_past'     => $isPast,
                'is_current'  => $isCurrent,
                'is_future'   => !$isPast && !$isCurrent,
                'status'      => $statusBreakdown,
                'sites'       => $siteBreakdown,
            ];
        }

        // Summary stats
        $overdue = $records->filter(fn ($r) => ($r->days_until_harvest ?? PHP_INT_MAX) < 0)->count();
        $readySoon = $records->filter(fn ($r) => ($r->days_until_harvest ?? PHP_INT_MAX) >= 0 && ($r->days_until_harvest ?? PHP_INT_MAX) <= 30)->count();
        $upcoming = $records->filter(fn ($r) => ($r->days_until_harvest ?? PHP_INT_MAX) > 30)->count();

        return [
            'months'     => $months,
            'overdue'    => $overdue,
            'ready_soon' => $readySoon,
            'upcoming'   => $upcoming,
            'total'      => $records->count(),
        ];
    }
}
