<?php
namespace App\Filament\Resources\PollenProductionResource\Pages;
use App\Filament\Resources\PollenProductionResource;
use Filament\Resources\Pages\ListRecords;
class ListPollenProductions extends ListRecords
{
    protected static string $resource = PollenProductionResource::class;
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('reports_overview')
                ->label('View Reports Overview')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->url(fn () => \App\Filament\Pages\ReportsDashboard::getUrl(['category' => 'pollen_production'])),
            \Filament\Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // PollenProductionResource\Widgets\PollenStockWidget::class,
        ];
    }
}
