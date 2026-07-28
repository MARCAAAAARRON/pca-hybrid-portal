<?php

namespace App\Filament\Resources\EventDocumentationResource\Pages;

use App\Filament\Resources\EventDocumentationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEventDocumentation extends CreateRecord
{
    protected static string $resource = EventDocumentationResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
