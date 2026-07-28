<?php

namespace App\Filament\Resources\EventDocumentationResource\Pages;

use App\Filament\Resources\EventDocumentationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEventDocumentation extends EditRecord
{
    protected static string $resource = EventDocumentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
