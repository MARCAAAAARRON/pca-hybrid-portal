<?php
namespace App\Filament\Resources\MonthlyHarvestResource\Pages;
use App\Filament\Resources\MonthlyHarvestResource;
use Filament\Resources\Pages\ListRecords;
class ListMonthlyHarvests extends ListRecords
{
    protected static string $resource = MonthlyHarvestResource::class;
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('reports_overview')
                ->label('View Reports Overview')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->url(fn () => \App\Filament\Pages\ReportsDashboard::getUrl(['category' => 'monthly_harvest'])),
            \Filament\Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // MonthlyHarvestResource\Widgets\HarvestForecastWidget::class,
            // MonthlyHarvestResource\Widgets\MonthlyProductionChart::class,
        ];
    }
}
