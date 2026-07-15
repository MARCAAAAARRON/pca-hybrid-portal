<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\UserResource\Pages;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;
use Filament\Infolists\Components\Section as InfolistSection;
use Illuminate\Validation\Rules\Password;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 101;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(
                    'User Information'
                )->schema([
                    TextInput::make('name')
                        ->required(),
                    TextInput::make('email')
                        ->required()
                        ->email()
                        ->unique(ignoreRecord: true),
                    TextInput::make('password')
                        ->password()
                        ->required(fn($livewire) => $livewire instanceof Pages\CreateUser)
                        ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                        ->dehydrated(fn($state) => filled($state))
                        ->rule(Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised())
                        ->revealable()
                        ->helperText('Must be at least 8 characters long and contain uppercase, lowercase, numbers, and symbols.'),
                    Select::make('role')
                        ->options(User::ROLE_CHOICES)
                        ->required()
                        ->native(false)
                        ->disabled(fn (?User $record): bool => $record !== null && $record->id === auth()->id()),
                    Select::make('field_site_id')
                        ->relationship('fieldSite', 'name')
                        ->label('Assigned Field Site')
                        ->placeholder('Select a field site')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required(fn(callable $get) => $get('role') === 'supervisor')
                        ->validationMessages([
                            'required' => 'A field site is required for supervisors.',
                        ]),
                    \Filament\Forms\Components\Toggle::make('is_approved')
                        ->label('Approved for Access')
                        ->default(false)
                        ->inline(false),
                ])->columns(2),
            ]);
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\ImageColumn::make('avatar_url')
                        ->searchable()
                        ->circular()
                        ->grow(false)
                        ->getStateUsing(fn($record) => $record->avatar_url
                            ? $record->avatar_url
                            : "https://ui-avatars.com/api/?name=" . urlencode($record->name)),
                    Tables\Columns\TextColumn::make('name')
                        ->searchable()
                        ->weight(FontWeight::Bold),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('role_display')
                            ->label('Role')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'PCDM / Division Chief I' => 'danger',
                                'Senior Agriculturist' => 'warning',
                                'COS / Agriculturist' => 'success',
                                default => 'gray',
                            })
                            ->icon('heroicon-o-shield-check')
                            ->grow(false),
                        Tables\Columns\TextColumn::make('fieldSite.name')
                            ->label('Field Site')
                            ->placeholder('No Site Assigned')
                            ->icon('heroicon-m-map-pin')
                            ->grow(false),
                        Tables\Columns\TextColumn::make('email')
                            ->icon('heroicon-m-envelope')
                            ->searchable()
                            ->grow(false),
                    ])->alignStart()->visibleFrom('lg')->space(1),
                    Tables\Columns\ToggleColumn::make('is_approved')
                        ->label('Approved')
                        ->grow(false),
                ]),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('Set Role')
                    ->icon('heroicon-m-adjustments-vertical')
                    ->form([
                        Select::make('role')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(10)
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->name),
                    ])
                    ->disabled(fn (User $record): bool => $record->id === auth()->id()),
                // Impersonate::make(),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn (User $record): bool => $record->id === auth()->id()),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make()
                    ->disabled(fn (User $record): bool => $record->id === auth()->id()),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(UserExporter::class),
                ImportAction::make()
                    ->importer(UserImporter::class)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(fn (Tables\Actions\DeleteBulkAction $action) => $action->getRecords()->each(function (User $record) use ($action) {
                            if ($record->id === auth()->id()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Self-deletion is blocked.')
                                    ->body('You cannot delete your own account.')
                                    ->send();
                                $action->halt();
                            }
                        })),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->before(fn (Tables\Actions\ForceDeleteBulkAction $action) => $action->getRecords()->each(function (User $record) use ($action) {
                            if ($record->id === auth()->id()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Self-deletion is blocked.')
                                    ->body('You cannot permanently delete your own account.')
                                    ->send();
                                $action->halt();
                            }
                        })),
                ]),
                ExportBulkAction::make()
                    ->exporter(UserExporter::class)
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('User Information')->schema([
                    TextEntry::make('name'),
                    TextEntry::make('email'),
                ]),
            ]);
    }
}
