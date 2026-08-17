<?php

namespace App\Filament\Widgets;

use App\Models\FieldSite;
use App\Models\HybridizationRecord;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminSummaryStats extends BaseWidget
{
    protected static ?int $sort = 0; // Top of the page
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    public static function canView(): bool
    {
        return auth()->user()?->isManager() || auth()->user()?->isAdmin();
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $totalRecords = HybridizationRecord::count();
        
        $sites = FieldSite::all();
        
        $stats = [
            Stat::make('Total Records', $totalRecords)
                ->description('Across all field sites')
                ->icon('heroicon-o-document-duplicate')
                ->color('success')
                ->extraAttributes(['class' => 'stat-gradient-2']), // Greenish
        ];
        
        foreach ($sites as $index => $site) {
            $gradientClass = ($index % 2 == 0) ? 'stat-gradient-3' : 'stat-gradient-1'; // Blue vs Pinkish
            $stats[] = Stat::make($site->name ?? 'Field Site', HybridizationRecord::where('field_site_id', $site->id)->count())
                ->description('Total records')
                ->icon('heroicon-o-map-pin')
                ->extraAttributes(['class' => $gradientClass]);
        }

        return $stats;
    }
}
