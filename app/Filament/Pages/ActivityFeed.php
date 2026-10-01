<?php

namespace App\Filament\Pages;

use App\Models\HybridDistribution;
use App\Models\MonthlyHarvest;
use App\Models\NurseryOperation;
use App\Models\PollenProduction;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityFeed extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.activity-feed';
    
    // Hide from the main sidebar
    protected static bool $shouldRegisterNavigation = false;
    
    protected static ?string $title = 'All Recent Activity';

    public string $filterType = 'all';

    public function getActivitiesProperty(): Collection
    {
        $user = auth()->user();
        $isSupervisor = ($user?->isSupervisor() || $user?->isSubSupervisor());
        $siteId = $user?->field_site_id;

        $activities = collect();

        // Harvests
        if ($this->filterType === 'all' || $this->filterType === 'harvest') {
            $harvests = MonthlyHarvest::with('fieldSite')->latest()
                ->when($isSupervisor && $siteId, fn($q) => $q->where('field_site_id', $siteId))
                ->limit(50) // practical limit for a single view dump if not using true pagination
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
        }

        // Nursery
        if ($this->filterType === 'all' || $this->filterType === 'nursery') {
            $nursery = NurseryOperation::with('fieldSite')->latest()
                ->where('report_type', 'operation')
                ->when($isSupervisor && $siteId, fn($q) => $q->where('field_site_id', $siteId))
                ->limit(50)
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
        }

        // Distribution
        if ($this->filterType === 'all' || $this->filterType === 'distribution') {
            $distributions = HybridDistribution::with('fieldSite')->latest()
                ->when($isSupervisor && $siteId, fn($q) => $q->where('field_site_id', $siteId))
                ->limit(50)
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
        }

        // Pollen
        if ($this->filterType === 'all' || $this->filterType === 'pollen') {
            $pollen = PollenProduction::with('fieldSite')->latest()
                ->when($isSupervisor && $siteId, fn($q) => $q->where('field_site_id', $siteId))
                ->limit(50)
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
        }

        return $activities->sortByDesc('date')->take(100);
    }
}
