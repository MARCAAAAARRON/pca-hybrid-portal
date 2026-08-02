<?php

namespace App\Filament\Resources\FieldSiteResource\Pages;

use App\Filament\Resources\FieldSiteResource;
use Filament\Resources\Pages\ListRecords;

class ListFieldSites extends ListRecords
{
    protected static string $resource = FieldSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()->label('New Field Site'),
        ];
    }
}
