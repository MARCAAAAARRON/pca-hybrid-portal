<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FieldSiteResource\Pages;
use App\Models\FieldSite;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class FieldSiteResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = FieldSite::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 100;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Field Site Details')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(1),

                Forms\Components\Section::make('Report Signatories')
                    ->description('Configure default signatory names, titles, and labels displayed on generated Excel and PDF reports for this site.')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                // Prepared By
                                Forms\Components\Fieldset::make('Prepared By (e.g. Supervisor)')
                                    ->schema([
                                        Forms\Components\TextInput::make('prepared_by_label')
                                            ->label('Label')
                                            ->default('Prepared by:')
                                            ->required()
                                            ->maxLength(50),
                                        Forms\Components\TextInput::make('prepared_by_name')
                                            ->label('Full Name')
                                            ->placeholder('e.g. Juan Dela Cruz')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('prepared_by_title')
                                            ->label('Title/Designation')
                                            ->default('COS / Agriculturist')
                                            ->placeholder('e.g. COS / Agriculturist')
                                            ->maxLength(255),
                                    ])->columns(1),

                                // Reviewed By
                                Forms\Components\Fieldset::make('Reviewed By (e.g. Manager)')
                                    ->schema([
                                        Forms\Components\TextInput::make('reviewed_by_label')
                                            ->label('Label')
                                            ->default('Reviewed by:')
                                            ->required()
                                            ->maxLength(50),
                                        Forms\Components\TextInput::make('reviewed_by_name')
                                            ->label('Full Name')
                                            ->placeholder('e.g. Maria Santos')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('reviewed_by_title')
                                            ->label('Title/Designation')
                                            ->default('Senior Agriculturist')
                                            ->placeholder('e.g. Senior Agriculturist')
                                            ->maxLength(255),
                                    ])->columns(1),

                                // Noted By
                                Forms\Components\Fieldset::make('Noted By (e.g. Division Chief)')
                                    ->schema([
                                        Forms\Components\TextInput::make('noted_by_label')
                                            ->label('Label')
                                            ->default('Noted by:')
                                            ->required()
                                            ->maxLength(50),
                                        Forms\Components\TextInput::make('noted_by_name')
                                            ->label('Full Name')
                                            ->placeholder('e.g. Engr. Pedro Penduko')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('noted_by_title')
                                            ->label('Title/Designation')
                                            ->default('PCDM / Division Chief I')
                                            ->placeholder('e.g. PCDM / Division Chief I')
                                            ->maxLength(255),
                                    ])->columns(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->label('Archive')
                    ->icon('heroicon-m-archive-box'),
                Tables\Actions\RestoreAction::make()
                    ->label('Unarchive')
                    ->icon('heroicon-m-arrow-path'),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Archive Selected')
                        ->icon('heroicon-m-archive-box'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Unarchive Selected')
                        ->icon('heroicon-m-arrow-path'),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFieldSites::route('/'),
            'create' => Pages\CreateFieldSite::route('/create'),
            'view' => Pages\ViewFieldSite::route('/{record}'),
            'edit' => Pages\EditFieldSite::route('/{record}/edit'),
        ];
    }
}
