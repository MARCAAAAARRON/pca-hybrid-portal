<?php

namespace App\Filament\Resources\EventDocumentationResource\Pages;

use App\Filament\Resources\EventDocumentationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventDocumentations extends ListRecords
{
    protected static string $resource = EventDocumentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Event Documentation'),
        ];
    }
}
