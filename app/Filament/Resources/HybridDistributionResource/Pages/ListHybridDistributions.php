<?php
namespace App\Filament\Resources\HybridDistributionResource\Pages;
use App\Filament\Resources\HybridDistributionResource;
use Filament\Resources\Pages\ListRecords;
class ListHybridDistributions extends ListRecords
{
    protected static string $resource = HybridDistributionResource::class;
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('reports_overview')
                ->label('View Reports Overview')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->url(fn () => \App\Filament\Pages\ReportsDashboard::getUrl(['category' => 'hybrid_distribution'])),
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
