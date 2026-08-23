<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NurseryOperationResource\Pages;
use App\Filament\Resources\NurseryOperationResource\RelationManagers;
use App\Models\NurseryOperation;
use App\Filament\Traits\HasApprovalActions;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NurseryOperationResource extends Resource implements HasShieldPermissions
{
    use HasApprovalActions;
    protected static ?string $model = NurseryOperation::class;

    protected static ?string $navigationIcon = 'heroicon-o-sun';

    protected static ?string $navigationGroup = 'Field Data';

    protected static ?string $navigationLabel = 'Nursery Operations';

    protected static ?string $modelLabel = 'Nursery Operation';

    protected static ?string $pluralModelLabel = 'Nursery Operations';

    protected static ?int $navigationSort = 4;

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Discrepancy Remarks')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Forms\Components\Placeholder::make('return_remarks')
                            ->label('Reviewer Feedback')
                            ->content(fn ($record) => $record?->return_remarks)
                    ])
                    ->visible(fn ($record) => $record && $record->isDraft() && !empty($record->return_remarks))
                    ->collapsible()
                    ->columnSpanFull(),

                Forms\Components\Section::make('Nursery Details')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('field_site_display')
                                ->label('Field Site')
                                ->default(fn () => auth()->user()->fieldSite?->name ?? 'None Assigned')
                                ->disabled()
                                ->dehydrated(false)
                                ->visible(fn () => auth()->user()?->isSupervisor())
                                ->columnSpanFull(),

                            Forms\Components\Select::make('field_site_id')
                                ->label('Field Site')
                                ->relationship('fieldSite', 'name')
                                ->required(fn () => !auth()->user()?->isSupervisor())
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->live()
                                ->visible(fn () => !auth()->user()?->isSupervisor())
                                ->columnSpanFull(),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('loadPrevious')
                                        ->label('Load from Previous Month')
                                        ->icon('heroicon-o-arrow-path')
                                        ->color('success')
                                        ->outlined()
                                        ->size('sm')
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $siteId = $get('field_site_id') ?: auth()->user()->field_site_id;
                                            if (!$siteId) {
                                                \Filament\Notifications\Notification::make()->warning()->title('Please select a Field Site first.')->send();
                                                return;
                                            }
                                            
                                            $latest = \App\Models\NurseryOperation::with('batches.varieties')
                                                ->where('field_site_id', $siteId)
                                                ->where('report_type', 'operation')
                                                ->orderBy('report_month', 'desc')
                                                ->first();
                                                
                                            if (!$latest) {
                                                \Filament\Notifications\Notification::make()->warning()->title('No previous records found for this site.')->send();
                                                return;
                                            }
                                            
                                            if ($latest->report_month) {
                                                $set('report_month', $latest->report_month->copy()->addMonth()->startOfMonth()->format('Y-m-d'));
                                            }
                                            $set('region_province_district', $latest->region_province_district);
                                            $set('barangay_municipality', $latest->barangay_municipality);
                                            $set('proponent_entity', $latest->proponent_entity);
                                            $set('proponent_representative', $latest->proponent_representative);
                                            $set('target_seednuts', $latest->target_seednuts);
                                            
                                            if ($latest->batches->isNotEmpty()) {
                                                $batches = $latest->batches->map(function ($b) {
                                                    return [
                                                        'seednuts_harvested' => $b->seednuts_harvested,
                                                        'date_harvested' => $b->date_harvested,
                                                        'date_received' => $b->date_received,
                                                        'source_of_seednuts' => $b->source_of_seednuts,
                                                        'varieties' => $b->varieties->map(function ($v) {
                                                            return [
                                                                'variety' => $v->variety,
                                                                'seednuts_sown' => 0,
                                                                'date_sown' => null,
                                                                'seedlings_germinated' => 0,
                                                                'ungerminated_seednuts' => 0,
                                                                'culled_seedlings' => 0,
                                                                'good_seedlings' => 0,
                                                                'ready_to_plant' => 0,
                                                                'seedlings_dispatched' => 0,
                                                                'remarks' => '',
                                                            ];
                                                        })->toArray(),
                                                    ];
                                                })->toArray();
                                                $set('batches', $batches);
                                            }
                                            
                                            \Filament\Notifications\Notification::make()->success()->title('Loaded from previous record.')->send();
                                        })
                                ]),

                                Forms\Components\Placeholder::make('previous_month_note')
                                    ->label('')
                                    ->content(function (Forms\Get $get) {
                                        $siteId = $get('field_site_id') ?: auth()->user()?->field_site_id;
                                        if (!$siteId) {
                                            return new \Illuminate\Support\HtmlString('');
                                        }
                                        $latest = \App\Models\NurseryOperation::where('field_site_id', $siteId)
                                            ->where('report_type', 'operation')
                                            ->orderBy('report_month', 'desc')
                                            ->first();
                                        if (!$latest || !$latest->report_month) {
                                            return new \Illuminate\Support\HtmlString(
                                                '<div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:8px;font-size:13px;color:#fca5a5;">'
                                                . '<svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                                                . '<span>No previous month record found for this site.</span>'
                                                . '</div>'
                                            );
                                        }
                                        $date = $latest->report_month->format('F d, Y');
                                        return new \Illuminate\Support\HtmlString(
                                            '<div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.3);border-radius:8px;font-size:13px;color:#6ee7b7;">'
                                            . '<svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                                            . '<span><strong>Note:</strong> There is a record from the previous month &mdash; <strong>' . $date . '</strong></span>'
                                            . '</div>'
                                        );
                                    }),
                            ])->columnSpanFull(),
                        ])->columnSpan(1),

                        Forms\Components\DatePicker::make('report_month')
                            ->label('Report Month')
                            ->required()
                            ->displayFormat('m / d / Y')
                            ->default(now()->startOfMonth())
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('region_province_district')
                            ->label('Region / Province / District')
                            ->placeholder('e.g. VII-Bohol/III')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('barangay_municipality')
                            ->label('Barangay / Municipality')
                            ->placeholder('e.g. Ballihan')
                            ->maxLength(200),

                        Forms\Components\Select::make('report_type')
                            ->options(\App\Models\NurseryOperation::REPORT_TYPES)
                            ->required()
                            ->default('operation')
                            ->disabled()
                            ->visible(false),
                    ])->columns(3),

                Forms\Components\Section::make('Proponent')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Forms\Components\TextInput::make('proponent_entity')
                            ->label('Entity Name')
                            ->placeholder('e.g. Ballihan On-Farm')
                            ->maxLength(200),
                        Forms\Components\TextInput::make('proponent_representative')
                            ->label('Representative')
                            ->placeholder('e.g. Epigenio M. Mahinay')
                            ->maxLength(200),
                        Forms\Components\TextInput::make('target_seednuts')
                            ->label('Target No. of Seednuts')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),

                Forms\Components\Section::make('Seednut Batches / Varieties')
                    ->description('Add harvest batches and their varieties')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([
                        Forms\Components\Repeater::make('batches')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('seednuts_harvested')
                                    ->label('No. Harvested')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('date_harvested')
                                    ->label('Date Harvested')
                                    ->placeholder('e.g. August 27, 2025')
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('date_received')
                                    ->label('Date Received')
                                    ->placeholder('e.g. August 28, 2025')
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('source_of_seednuts')
                                    ->label('Source of Seednuts')
                                    ->maxLength(200),

                                // Nested repeater: Varieties within this batch
                                Forms\Components\Repeater::make('varieties')
                                    ->relationship()
                                    ->label('Varieties within this batch')
                                    ->schema([
                                        Forms\Components\TextInput::make('variety')
                                            ->label('Variety / Type')
                                            ->placeholder('e.g. PCA 15-10')
                                            ->maxLength(100),
                                        Forms\Components\TextInput::make('seednuts_sown')
                                            ->label('No. Sown')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->default(0),
                                        Forms\Components\TextInput::make('date_sown')
                                            ->label('Date Sown')
                                            ->placeholder('e.g. Sept 11, 2025')
                                            ->maxLength(50),
                                        Forms\Components\TextInput::make('seedlings_germinated')
                                            ->label('No. Germinated')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->default(0),
                                        Forms\Components\TextInput::make('ungerminated_seednuts')
                                            ->label('No. Ungerminated')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->default(0),
                                        Forms\Components\TextInput::make('culled_seedlings')
                                            ->label('No. Culled Seedlings')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->default(0),
                                        Forms\Components\TextInput::make('good_seedlings')
                                            ->label('Good Seedlings @ 1 ft')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->default(0),
                                        Forms\Components\TextInput::make('ready_to_plant')
                                            ->label('Ready to Plant (Polybagged)')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->default(0),
                                        Forms\Components\TextInput::make('seedlings_dispatched')
                                            ->label('Seedlings Dispatched')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->default(0),
                                        Forms\Components\TextInput::make('remarks')
                                            ->label('Remarks')
                                            ->maxLength(255),
                                    ])
                                    ->columns(4)
                                    ->addActionLabel('Add Variety')
                                    ->defaultItems(1)
                                    ->reorderable(false)
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->addActionLabel('Add Batch')
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                'Harvest Batch — ' . ($state['seednuts_harvested'] ?? 0) . ' seednuts'
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fieldSite.name')
                    ->label('Field Site')
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_month')
                    ->date('F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'operation' => 'info',
                        'terminal' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => NurseryOperation::REPORT_TYPES[$state] ?? $state),
                Tables\Columns\TextColumn::make('proponent_entity')
                    ->label('Proponent')
                    ->searchable(),
                Tables\Columns\TextColumn::make('proponent_representative')
                    ->label('Representative')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('target_seednuts')
                    ->label('Target Seednuts')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('batches_count')
                    ->counts('batches')
                    ->label('Batches'),
                self::getStatusColumn(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('field_site_id')
                    ->label('Field Site')
                    ->relationship('fieldSite', 'name'),
                Tables\Filters\SelectFilter::make('report_type')
                    ->options(NurseryOperation::REPORT_TYPES),
                Tables\Filters\Filter::make('report_year')
                    ->form([
                        Forms\Components\Select::make('year')
                            ->options(fn () => collect(range(now()->year, 2024, -1))
                                ->mapWithKeys(fn ($y) => [$y => $y]))
                            ->default(now()->year),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['year'] ?? null, fn ($q, $year) =>
                            $q->whereYear('report_month', $year)
                        )
                    ),
                self::getStatusFilter(),
            ])
            ->defaultSort('report_month', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Model $record) => $record->isDraft() && auth()->user()?->isSupervisor()),
                Tables\Actions\DeleteAction::make()
                    ->label('Archive')
                    ->icon('heroicon-m-archive-box')
                    ->visible(fn (Model $record) => 
                        ($record->isDraft() && auth()->user()?->isSupervisor()) ||
                        ($record->isNoted() && in_array(auth()->user()?->role, ['admin', 'superadmin']))
                    ),
                Tables\Actions\RestoreAction::make()
                    ->label('Unarchive')
                    ->icon('heroicon-m-arrow-path'),
                Tables\Actions\ForceDeleteAction::make(),
                ...self::getApprovalActions(),
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
            ])
            ;
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('General Information')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('fieldSite.name')->label('Field Site'),
                        \Filament\Infolists\Components\TextEntry::make('report_month')->date('F Y')->label('Report Month'),
                        \Filament\Infolists\Components\TextEntry::make('proponent_entity')->label('Proponent Entity'),
                        \Filament\Infolists\Components\TextEntry::make('proponent_representative')->label('Representative'),
                        \Filament\Infolists\Components\TextEntry::make('target_seednuts')->label('Target Seednuts')->numeric(),
                        \Filament\Infolists\Components\TextEntry::make('status')->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'submitted' => 'warning',
                                'validated' => 'success',
                                'revision' => 'danger',
                                default => 'gray',
                            }),
                    ])->columns(3),

                \Filament\Infolists\Components\Section::make('Audit Trail & Verification Timeline')
                    ->description('Complete lifecycle of this record')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        \Filament\Infolists\Components\ViewEntry::make('audit_timeline')
                            ->hiddenLabel()
                            ->view('filament.infolists.audit-timeline')
                    ])->columnSpanFull(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->where('report_type', 'operation')
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
        
        if (auth()->user()?->isSupervisor()) {
            $query->where('field_site_id', auth()->user()->field_site_id);
        }
        return $query;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\NurseryBatchesRelationManager::class,
            ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNurseryOperations::route('/'),
            'create' => Pages\CreateNurseryOperation::route('/create'),
            'edit' => Pages\EditNurseryOperation::route('/{record}/edit'),
        ];
    }
}
