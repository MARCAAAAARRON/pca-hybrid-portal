<?php

namespace App\Filament\Widgets;

use App\Models\FieldSite;
use App\Models\HybridDistribution;
use App\Models\MonthlyHarvest;
use App\Models\NurseryOperation;
use App\Models\PollenProduction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class PerFarmBreakdown extends BaseWidget
{
    protected static ?int $sort = 4;
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
        $this->resetTable();
    }

    public static function canView(): bool
    {
        if (auth()->user()?->role === 'sub_supervisor') return false;
        return auth()->user()?->isManager() || auth()->user()?->isAdmin();
    }

    public function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        $y = $this->year ?? now()->year;
        return "Per-Farm Breakdown — {$y}";
    }

    public function table(Table $table): Table
    {
        $year = $this->year ?? (int) now()->year;

        return $table
            ->query(FieldSite::query())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('FARM / FIELD SITE')
                    ->icon('heroicon-m-map-pin')
                    ->iconColor('primary')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('harvest_count')
                    ->label('HARVEST')
                    ->getStateUsing(fn($record) => MonthlyHarvest::where('field_site_id', $record->id)->whereYear('report_month', $year)->count())
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('nursery_count')
                    ->label('NURSERY')
                    ->getStateUsing(fn($record) => NurseryOperation::where('field_site_id', $record->id)->where('report_type', 'operation')->whereYear('report_month', $year)->count())
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('terminal_count')
                    ->label('TERMINAL')
                    ->getStateUsing(fn($record) => NurseryOperation::where('field_site_id', $record->id)->where('report_type', 'terminal')->whereYear('report_month', $year)->count())
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('pollen_count')
                    ->label('POLLEN')
                    ->getStateUsing(fn($record) => PollenProduction::where('field_site_id', $record->id)->whereYear('report_month', $year)->count())
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('distribution_count')
                    ->label('DISTRIBUTION')
                    ->getStateUsing(fn($record) => HybridDistribution::where('field_site_id', $record->id)->whereYear('report_month', $year)->count())
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('total_count')
                    ->label('TOTAL')
                    ->weight('bold')
                    ->getStateUsing(fn($record) => 
                        MonthlyHarvest::where('field_site_id', $record->id)->whereYear('report_month', $year)->count() +
                        NurseryOperation::where('field_site_id', $record->id)->whereYear('report_month', $year)->count() +
                        PollenProduction::where('field_site_id', $record->id)->whereYear('report_month', $year)->count() +
                        HybridDistribution::where('field_site_id', $record->id)->whereYear('report_month', $year)->count()
                    )
                    ->alignment('center'),
            ])
            ->paginated(false);
    }
}
