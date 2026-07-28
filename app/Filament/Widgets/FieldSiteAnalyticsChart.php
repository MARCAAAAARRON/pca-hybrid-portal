<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class FieldSiteAnalyticsChart extends ChartWidget
{
    protected static ?string $heading = 'Field Site Analytics (Harvest Trend)';
    protected static ?int $sort = -6;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '400px';

    public ?int $year = null;

    public function mount(): void
    {
        $this->year = (int) now()->year;
    }

    #[\Livewire\Attributes\On('dashboard-year-changed')]
    public function onYearChanged(int $year): void
    {
        $this->year = $year;
    }

    public static function canView(): bool
    {
        if (auth()->user()?->role === 'sub_supervisor') return false;
        return auth()->user()?->isManager() || auth()->user()?->isAdmin();
    }

    protected function getData(): array
    {
        $year = $this->year ?? (int) now()->year;
        $sites = \App\Models\FieldSite::all();
        
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $datasets = [];

        $colors = ['#16a34a', '#2563eb', '#ea580c', '#9333ea', '#dc2626', '#0ea5e9', '#f59e0b'];
        
        foreach ($sites as $index => $site) {
            $data = [];
            for ($month = 1; $month <= 12; $month++) {
                $total = \App\Models\HarvestVariety::whereHas('monthlyHarvest', function ($q) use ($site, $year, $month) {
                    $q->withoutGlobalScopes()
                      ->where('field_site_id', $site->id)
                      ->whereYear('report_month', $year)
                      ->whereMonth('report_month', $month);
                })->sum('seednuts_count');
                
                $data[] = $total;
            }
            
            $color = $colors[$index % count($colors)];
            
            $datasets[] = [
                'label' => $site->name,
                'data' => $data,
                'borderColor' => $color,
                'backgroundColor' => 'transparent',
                'borderWidth' => 2,
                'tension' => 0.3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
