<?php

namespace App\Filament\Resources\FieldSiteResource\Pages;

use App\Filament\Resources\FieldSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\HtmlString;

class ViewFieldSite extends ViewRecord
{
    protected static string $resource = FieldSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Site Information')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Site Name')
                            ->weight('bold')
                            ->size('lg'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->default('—'),
                        Infolists\Components\TextEntry::make('users_count')
                            ->label('Assigned Users')
                            ->getStateUsing(fn ($record) => $record->users()->count()),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }
}
