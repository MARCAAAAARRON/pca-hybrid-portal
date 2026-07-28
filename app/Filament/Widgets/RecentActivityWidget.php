<?php

namespace App\Filament\Widgets;

use App\Models\HybridDistribution;
use App\Models\MonthlyHarvest;
use App\Models\NurseryOperation;
use App\Models\PollenProduction;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class RecentActivityWidget extends Widget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.recent-activity-widget';

    public static function canView(): bool
    {
        if (auth()->user()?->role === 'sub_supervisor') return false;
        return auth()->user()?->isSupervisor();
    }

    public function getActivities(): Collection
    {
        $user = auth()->user();
        $isSupervisor = $user?->isSupervisor();
        $siteId = $user?->field_site_id;

        $activities = collect();

        // Harvests
        $harvests = MonthlyHarvest::with('fieldSite')->latest()
            ->when($isSupervisor && $siteId, fn($q) => $q->where('field_site_id', $siteId))
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'type' => 'Harvest',
                'date' => $item->created_at,
                'title' => ($item->fieldSite?->name ?? 'Unknown Site') . " — " . ($item->report_month ? $item->report_month->format('M Y') : 'N/A'),
                'desc' => "Produced " . ($item->total_production ?? 0) . " seednuts",
                'user' => 'System',
                'color' => 'success',
            ]);
        $activities = $activities->concat($harvests);

        // Nursery
        $nursery = NurseryOperation::with('fieldSite')->latest()
            ->where('report_type', 'operation')
            ->when($isSupervisor && $siteId, fn($q) => $q->where('field_site_id', $siteId))
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'type' => 'Nursery',
                'date' => $item->created_at,
                'title' => ($item->fieldSite?->name ?? 'Unknown Site') . " — " . ($item->report_month ? $item->report_month->format('M Y') : 'N/A'),
                'desc' => "Nursery Operation updated",
                'user' => 'System',
                'color' => 'primary',
            ]);
        $activities = $activities->concat($nursery);

        // Distribution
        $distributions = HybridDistribution::with('fieldSite')->latest()
            ->when($isSupervisor && $siteId, fn($q) => $q->where('field_site_id', $siteId))
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'type' => 'Distribution',
                'date' => $item->created_at,
                'title' => ($item->fieldSite?->name ?? 'Unknown Site') . " — " . ($item->full_name ?: 'General Distribution'),
                'desc' => "Distributed " . ($item->seedlings_received ?? 0) . " seedlings",
                'user' => 'System',
                'color' => 'info',
            ]);
        $activities = $activities->concat($distributions);

        // Pollen
        $pollen = PollenProduction::with('fieldSite')->latest()
            ->when($isSupervisor && $siteId, fn($q) => $q->where('field_site_id', $siteId))
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'type' => 'Pollen',
                'date' => $item->created_at,
                'title' => ($item->fieldSite?->name ?? 'Unknown Site') . " — " . ($item->report_month ? $item->report_month->format('M Y') : 'N/A'),
                'desc' => "Pollen extraction completed",
                'user' => 'System',
                'color' => 'warning',
            ]);
        $activities = $activities->concat($pollen);

        return $activities->sortByDesc('date')->take(2);
    }
}
