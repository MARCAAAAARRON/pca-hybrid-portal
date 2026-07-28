<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventDocumentationResource\Pages;
use App\Filament\Resources\EventDocumentationResource\RelationManagers;
use App\Models\EventDocumentation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class EventDocumentationResource extends Resource
{
    protected static ?string $model = EventDocumentation::class;

    protected static ?string $navigationGroup = 'Strategic Documentation';

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    protected static ?string $navigationLabel = 'PCA Events & Milestones';

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['manager', 'admin', 'superadmin']);
    }

    public static function canCreate(): bool
    {
        return in_array(auth()->user()?->role, ['manager', 'admin', 'superadmin']);
    }

    public static function canEdit(Model $record): bool
    {
        return in_array(auth()->user()?->role, ['manager', 'admin', 'superadmin']);
    }

    public static function canDelete(Model $record): bool
    {
        return in_array(auth()->user()?->role, ['manager', 'admin', 'superadmin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Event Details')->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('event_date')
                        ->required(),
                    Forms\Components\TextInput::make('location')
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Event Photo')
                        ->image()
                        ->directory('event-photos')
                        ->columnSpanFull(),
                    Forms\Components\Hidden::make('uploaded_by')
                        ->default(fn() => auth()->id()),
                ])->columns(2),
                
                Forms\Components\Section::make('EXIF Data (Auto-extracted)')->schema([
                    Forms\Components\TextInput::make('latitude')
                        ->disabled(),
                    Forms\Components\TextInput::make('longitude')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('captured_at')
                        ->disabled(),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Photo')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('captured_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventDocumentations::route('/'),
            'create' => Pages\CreateEventDocumentation::route('/create'),
            'edit' => Pages\EditEventDocumentation::route('/{record}/edit'),
        ];
    }
}
