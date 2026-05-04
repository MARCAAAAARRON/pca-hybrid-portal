<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonthlyHarvestResource\Pages;
use App\Filament\Resources\MonthlyHarvestResource\RelationManagers;
use App\Models\MonthlyHarvest;
use App\Filament\Traits\HasApprovalActions;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonthlyHarvestResource extends Resource implements HasShieldPermissions
{
    use HasApprovalActions;
    protected static ?string $model = MonthlyHarvest::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Field Data';

    protected static ?string $navigationLabel = 'Monthly Harvest';

    protected static ?int $navigationSort = 3;

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Harvest Details')
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
                                        
                                        $latest = \App\Models\MonthlyHarvest::with('varieties')
                                            ->where('field_site_id', $siteId)
                                            ->orderBy('report_month', 'desc')
                                            ->first();
                                            
                                        if (!$latest) {
                                            \Filament\Notifications\Notification::make()->warning()->title('No previous records found for this site.')->send();
                                            return;
                                        }
                                        
                                        if ($latest->report_month) {
                                            $set('report_month', $latest->report_month->copy()->addMonth()->startOfMonth()->format('Y-m-d'));
                                        }
                                        $set('location', $latest->location);
                                        $set('farm_name', $latest->farm_name);
                                        $set('area_ha', $latest->area_ha);
                                        $set('age_of_palms', $latest->age_of_palms);
                                        $set('num_hybridized_palms', $latest->num_hybridized_palms);
                                        
                                        // Carry forward varieties
                                        if ($latest->varieties->isNotEmpty()) {
                                            $varieties = $latest->varieties->map(function ($v) {
                                                return [
                                                    'variety' => $v->variety,
                                                    'seednuts_type' => $v->seednuts_type,
                                                    'seednuts_count' => 0,
                                                    'remarks' => '',
                                                ];
                                            })->toArray();
                                            $set('varieties', $varieties);
                                        }
                                        
                                        \Filament\Notifications\Notification::make()->success()->title('Loaded from previous record.')->send();
                                    })
                            ]),
                        ])->columnSpan(1),

                        Forms\Components\DatePicker::make('report_month')
                            ->label('Report Month')
                            ->required()
                            ->displayFormat('m / d / Y')
                            ->default(now()->startOfMonth())
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('location')
                            ->label('Farm Location')
                            ->placeholder('e.g. Brgy. Boctol, Ballihan, Bohol')
                            ->maxLength(200)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('farm_name')
                            ->label('Name of Partner / Farm')
                            ->placeholder('e.g. Violo Llorente, Sr.')
                            ->maxLength(200)
                            ->columnSpan(1),
                    ])->columns(4),

                Forms\Components\Section::make('Farm Details')
                    ->icon('heroicon-o-map')
                    ->schema([
                        Forms\Components\TextInput::make('area_ha')
                            ->label('Area (Ha.)')
                            ->placeholder('e.g. 3.62')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('age_of_palms')
                            ->label('Age of Palms (Years)')
                            ->placeholder('e.g. 16')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('num_hybridized_palms')
                            ->label('No. of Hybridized Palms')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),

                Forms\Components\Section::make('Variety / Hybrid Crosses')
                    ->description('Add one or more — enter seednuts count for this month')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Forms\Components\Repeater::make('varieties')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('variety')
                                    ->label('Variety / Hybrid Crosses')
                                    ->placeholder('e.g. Catigan Green Dwarf')
                                    ->required()
                                    ->maxLength(200),
                                Forms\Components\Select::make('seednuts_type')
                                    ->label('Seednuts Produced')
                                    ->options([
                                        'OPV' => 'OPV',
                                        'Hybrid' => 'Hybrid',
                                    ])
                                    ->default('OPV')
                                    ->required(),
                                Forms\Components\TextInput::make('seednuts_count')
                                    ->label('Seednuts Count')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                Forms\Components\TextInput::make('remarks')
                                    ->label('Remarks')
                                    ->placeholder('Optional remarks')
                                    ->maxLength(500),
                            ])
                            ->columns(4)
                            ->addActionLabel('Add Variety')
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                ($state['variety'] ?? '') . ' — ' . ($state['seednuts_count'] ?? 0) . ' seednuts'
                            ),
                    ]),


                Forms\Components\Textarea::make('remarks')->label('Remarks')->rows(3)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fieldSite.name')
                    ->label('SITE')
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_month')
                    ->label('REPORT MONTH')
                    ->date('M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('FARM LOCATION')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('farm_name')
                    ->label('NAME OF PARTNER/FARM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('area_ha')
                    ->label('AREA (HA.)')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('age_of_palms')
                    ->label('AGE OF PALMS')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('num_hybridized_palms')
                    ->label('HYBRIDIZED PALMS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('varieties.variety')
                    ->label('VARIETY / HYBRID CROSSES')
                    ->listWithLineBreaks()
                    ->bulleted(),
                Tables\Columns\TextColumn::make('varieties.seednuts_type')
                    ->label('OPV/ HYBRID')
                    ->listWithLineBreaks(),
                Tables\Columns\TextColumn::make('varieties.seednuts_count')
                    ->label('SEEDNUTS COUNT')
                    ->listWithLineBreaks(),
                Tables\Columns\TextColumn::make('total_production')
                    ->label('TOTAL SEEDNUTS')
                    ->numeric()
                    ->sortable(false),
                self::getStatusColumn()
                    ->label('STATUS'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('field_site_id')
                    ->label('Field Site')
                    ->relationship('fieldSite', 'name'),
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
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (\App\Models\MonthlyHarvest $record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.monthly-harvest-report', [
                            'record' => $record->loadMissing('fieldSite', 'varieties', 'preparedBy', 'reviewedBy')
                        ]);
                        
                        $filename = 'Harvest_Report_' . $record->report_month->format('Y_m') . '_' . \Illuminate\Support\Str::slug($record->fieldSite->name) . '.pdf';
                        return response()->streamDownload(fn () => print($pdf->output()), $filename);
                    }),
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
                    ->exporter(\App\Filament\Exports\MonthlyHarvestExporter::class),
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
                        $query = \App\Models\MonthlyHarvest::query();
                        
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
                        
                        $records = $query->with(['fieldSite', 'varieties'])->get();
                        
                        if ($records->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('No records found for the selected filters.')
                                ->send();
                            return;
                        }

                        $isCumulative = ($data['export_range'] ?? 'single') === 'cumulative';
                        return (new \App\Exports\MonthlyHarvestExport($records, $data['year'], $data['month'], $isCumulative))->export();
                    }),
            ]);
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('General Information')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('fieldSite.name')->label('Field Site'),
                        \Filament\Infolists\Components\TextEntry::make('report_month')->date('F Y'),
                        \Filament\Infolists\Components\TextEntry::make('status')->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'submitted' => 'warning',
                                'validated' => 'success',
                                'revision' => 'danger',
                                default => 'gray',
                            }),
                    ])->columns(3),

                \Filament\Infolists\Components\Section::make('Audit Timeline')
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
        $query = parent::getEloquentQuery();

        if (auth()->user()?->isSupervisor()) {
            $query->where('field_site_id', auth()->user()->field_site_id);
        }

        return $query;
    }

    public static function getWidgets(): array
    {
        return [
            // \App\Filament\Resources\MonthlyHarvestResource\Widgets\HarvestForecastWidget::class,
            // \App\Filament\Resources\MonthlyHarvestResource\Widgets\MonthlyProductionChart::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HarvestVarietiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonthlyHarvests::route('/'),
            'create' => Pages\CreateMonthlyHarvest::route('/create'),
            'edit' => Pages\EditMonthlyHarvest::route('/{record}/edit'),
        ];
    }
}
