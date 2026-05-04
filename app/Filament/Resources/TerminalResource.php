<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TerminalResource\Pages;
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

class TerminalResource extends Resource implements HasShieldPermissions
{
    use HasApprovalActions;
    protected static ?string $model = NurseryOperation::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Field Data';

    protected static ?string $navigationLabel = 'Terminal Reports';
    
    protected static ?string $modelLabel = 'Terminal Report';

    protected static ?string $pluralModelLabel = 'Terminal Reports';
    
    protected static ?string $slug = 'terminal-reports';

    protected static ?int $navigationSort = 5;

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Terminal Report Details')
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
                                ->visible(fn () => !auth()->user()?->isSupervisor())
                                ->columnSpanFull(),

                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('loadFromNursery')
                                    ->label('Load from Nursery Records')
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
                                            \Filament\Notifications\Notification::make()->warning()->title('No previous Nursery records found for this site.')->send();
                                            return;
                                        }

                                        if ($latest->report_month) {
                                            $set('report_month', $latest->report_month->format('Y-m-d'));
                                        }
                                        $set('region_province_district', $latest->region_province_district);
                                        $set('barangay_municipality', $latest->barangay_municipality);
                                        $set('proponent_entity', $latest->proponent_entity);
                                        $set('proponent_representative', $latest->proponent_representative);
                                        $set('target_seednuts', $latest->target_seednuts);

                                        if ($latest->batches->isNotEmpty()) {
                                            $earliestSown = null;
                                            $batches = $latest->batches->map(function ($b) use (&$earliestSown) {
                                                return [
                                                    'seednuts_harvested' => $b->seednuts_harvested,
                                                    'date_harvested' => $b->date_harvested,
                                                    'date_received' => $b->date_received,
                                                    'source_of_seednuts' => $b->source_of_seednuts,
                                                    'varieties' => $b->varieties->map(function ($v) use (&$earliestSown) {
                                                        if ($v->date_sown) {
                                                            try {
                                                                $parsed = \Carbon\Carbon::parse($v->date_sown);
                                                                if (!$earliestSown || $parsed->lt($earliestSown)) {
                                                                    $earliestSown = $parsed;
                                                                }
                                                            // @phpstan-ignore-next-line
                                                            } catch (\Exception $e) {}
                                                        }
                                                        return [
                                                            'variety' => $v->variety,
                                                            'seednuts_sown' => $v->seednuts_sown,
                                                            'date_sown' => $v->date_sown,
                                                            'seedlings_germinated' => $v->seedlings_germinated,
                                                            'ungerminated_seednuts' => $v->ungerminated_seednuts,
                                                            'culled_seedlings' => $v->culled_seedlings,
                                                            'good_seedlings' => $v->good_seedlings,
                                                            'ready_to_plant' => $v->ready_to_plant,
                                                            'seedlings_dispatched' => $v->seedlings_dispatched,
                                                            'remarks' => $v->remarks,
                                                        ];
                                                    })->toArray(),
                                                ];
                                            })->toArray();
                                            $set('batches', $batches);
                                            
                                            if ($earliestSown) {
                                                $set('nursery_start_date', $earliestSown->format('Y-m-d'));
                                            }
                                        }

                                        \Filament\Notifications\Notification::make()->success()->title('Loaded from previous nursery operation.')->send();
                                    })
                            ]),
                        ])->columnSpan(1),

                        Forms\Components\DatePicker::make('report_month')
                            ->label('Report Month')
                            ->required()
                            ->displayFormat('m / d / Y')
                            ->default(now()->startOfMonth()),

                        Forms\Components\TextInput::make('region_province_district')
                            ->label('Region / Province / District')
                            ->placeholder('e.g. VII-Bohol/III')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('barangay_municipality')
                            ->label('Barangay / Municipality')
                            ->placeholder('e.g. Ballihan')
                            ->maxLength(200),
                        Forms\Components\Select::make('report_type')
                            ->options(NurseryOperation::REPORT_TYPES)
                            ->required()
                            ->default('terminal')
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

                Forms\Components\Section::make('Terminal Report Information')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\DatePicker::make('nursery_start_date')
                            ->label('Nursery Start Date')
                            ->helperText('When seednuts were first sown')
                            ->displayFormat('m / d / Y')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                $end = $get('date_ready_for_distribution');
                                if ($state && $end) {
                                    $set('seedling_age', \Carbon\Carbon::parse($state)->diffInMonths(\Carbon\Carbon::parse($end)) . ' months');
                                }
                            }),
                        Forms\Components\DatePicker::make('date_ready_for_distribution')
                            ->label('Date Ready for Distribution')
                            ->helperText('When seedlings became ready')
                            ->displayFormat('m / d / Y')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                $start = $get('nursery_start_date');
                                if ($state && $start) {
                                    $set('seedling_age', \Carbon\Carbon::parse($start)->diffInMonths(\Carbon\Carbon::parse($state)) . ' months');
                                }
                            }),
                        Forms\Components\TextInput::make('seedling_age')
                            ->label('Seedling Age')
                            ->helperText('Auto-calculated from dates')
                            ->placeholder('—')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get) {
                                $start = $get('nursery_start_date');
                                $end = $get('date_ready_for_distribution');
                                if ($start && $end) {
                                    $s = \Carbon\Carbon::parse($start);
                                    $e = \Carbon\Carbon::parse($end);
                                    $component->state($s->diffInMonths($e) . ' months');
                                }
                            }),
                    ])->columns(3),

                Forms\Components\Section::make('Seednut Batches / Varieties')
                    ->description('Add harvest batches and their varieties')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([
                        Forms\Components\Repeater::make('batches')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('seednuts_harvested')
                                    ->label('No. Harvested')->numeric()->default(0),
                                Forms\Components\TextInput::make('date_harvested')
                                    ->label('Date Harvested')->placeholder('e.g. August 27, 2025')->maxLength(50),
                                Forms\Components\TextInput::make('date_received')
                                    ->label('Date Received')->placeholder('e.g. August 28, 2025')->maxLength(50),
                                Forms\Components\TextInput::make('source_of_seednuts')
                                    ->label('Source of Seednuts')->maxLength(200),

                                Forms\Components\Repeater::make('varieties')
                                    ->relationship()
                                    ->label('Varieties within this batch')
                                    ->schema([
                                        Forms\Components\TextInput::make('variety')
                                            ->label('Variety / Type')->placeholder('e.g. PCA 15-10')->maxLength(100),
                                        Forms\Components\TextInput::make('seednuts_sown')
                                            ->label('No. Sown')->numeric()->required()->minValue(0)->default(0),
                                        Forms\Components\TextInput::make('date_sown')
                                            ->label('Date Sown')->placeholder('e.g. Sept 11, 2025')->maxLength(50),
                                        Forms\Components\TextInput::make('seedlings_germinated')
                                            ->label('No. Germinated')->numeric()->required()->minValue(0)->default(0),
                                        Forms\Components\TextInput::make('ungerminated_seednuts')
                                            ->label('No. Ungerminated')->numeric()->required()->minValue(0)->default(0),
                                        Forms\Components\TextInput::make('culled_seedlings')
                                            ->label('No. Culled Seedlings')->numeric()->required()->minValue(0)->default(0),
                                        Forms\Components\TextInput::make('good_seedlings')
                                            ->label('Good Seedlings @ 1 ft')->numeric()->required()->minValue(0)->default(0),
                                        Forms\Components\TextInput::make('ready_to_plant')
                                            ->label('Ready to Plant (Polybagged)')->numeric()->required()->minValue(0)->default(0),
                                        Forms\Components\TextInput::make('seedlings_dispatched')
                                            ->label('Seedlings Dispatched')->numeric()->required()->minValue(0)->default(0),
                                        Forms\Components\TextInput::make('remarks')
                                            ->label('Remarks')->maxLength(255),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                ...self::getApprovalActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(\App\Filament\Exports\NurseryOperationExporter::class),
                Tables\Actions\Action::make('formattedExport')
                    ->label('Formatted Export (Excel)')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('year')
                            ->options(fn () => collect(range(now()->year, 2024, -1))
                                ->mapWithKeys(fn ($y) => [$y => $y]))
                            ->default(now()->year)
                            ->required(),
                        Forms\Components\Select::make('month')
                            ->options([
                                1 => 'January', 2 => 'February', 3 => 'March',
                                4 => 'April', 5 => 'May', 6 => 'June',
                                7 => 'July', 8 => 'August', 9 => 'September',
                                10 => 'October', 11 => 'November', 12 => 'December',
                            ])
                            ->nullable()
                            ->live(),
                        Forms\Components\Radio::make('export_range')
                            ->label('Export Coverage')
                            ->options([
                                'single' => 'Selected Month Only',
                                'cumulative' => 'Cumulative (Jan to Selected Month)',
                            ])
                            ->default('single')
                            ->inline()
                            ->visible(fn (Forms\Get $get) => filled($get('month'))),
                        Forms\Components\Select::make('field_site_id')
                            ->label('Field Site')
                            ->relationship('fieldSite', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->hidden(fn () => auth()->user()?->isSupervisor())
                            ->default(fn () => auth()->user()?->isSupervisor() ? auth()->user()->field_site_id : null),
                    ])
                    ->action(function (array $data) {
                        $query = \App\Models\NurseryOperation::query()->where('report_type', 'terminal');
                        
                        $query->whereYear('report_month', $data['year']);
                        
                        if ($data['month']) {
                            if (($data['export_range'] ?? 'single') === 'cumulative') {
                                $query->whereMonth('report_month', '<=', $data['month']);
                            } else {
                                $query->whereMonth('report_month', $data['month']);
                            }
                        }
                        
                        if (auth()->user()?->isSupervisor()) {
                            $query->where('field_site_id', auth()->user()->field_site_id);
                        } elseif ($data['field_site_id']) {
                            $query->where('field_site_id', $data['field_site_id']);
                        }
                        
                        $records = $query->with(['fieldSite', 'batches.varieties'])->get();
                        
                        if ($records->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('No records found for the selected filters.')
                                ->send();
                            return;
                        }

                        $isCumulative = ($data['export_range'] ?? 'single') === 'cumulative';
                        return (new \App\Exports\NurseryOperationExport($records, $data['year'], $data['month'], $isCumulative))->export();
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->where('report_type', 'terminal');
        
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
            'index' => Pages\ListTerminalReports::route('/'),
            'create' => Pages\CreateTerminalReport::route('/create'),
            'edit' => Pages\EditTerminalReport::route('/{record}/edit'),
        ];
    }
}
