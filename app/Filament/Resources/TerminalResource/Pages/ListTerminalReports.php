<?php
namespace App\Filament\Resources\TerminalResource\Pages;

use App\Filament\Resources\TerminalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTerminalReports extends ListRecords
{
    protected static string $resource = TerminalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('reports_overview')
                ->label('View Reports Overview')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->url(fn () => \App\Filament\Pages\ReportsDashboard::getUrl(['category' => 'terminal_report'])),
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
