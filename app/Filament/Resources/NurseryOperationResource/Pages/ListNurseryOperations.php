<?php
namespace App\Filament\Resources\NurseryOperationResource\Pages;
use App\Filament\Resources\NurseryOperationResource;
use Filament\Resources\Pages\ListRecords;
class ListNurseryOperations extends ListRecords
{
    protected static string $resource = NurseryOperationResource::class;
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('reports_overview')
                ->label('View Reports Overview')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->url(fn () => \App\Filament\Pages\ReportsDashboard::getUrl(['category' => 'nursery_operation'])),
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
