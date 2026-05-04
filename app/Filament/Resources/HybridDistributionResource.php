<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HybridDistributionResource\Pages;
use App\Models\HybridDistribution;
use App\Models\FieldSite;
use App\Filament\Traits\HasApprovalActions;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HybridDistributionResource extends Resource implements HasShieldPermissions
{
    use HasApprovalActions;
    protected static ?string $model = HybridDistribution::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Field Data';

    protected static ?string $navigationLabel = 'Hybrid Distribution';

    protected static ?int $navigationSort = 2;

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    /**
     * Farmer-entry field schema used in both the Repeater (Create) and flat form (Edit).
     */
    public static function getFarmerFields(): array
    {
        return [
            Forms\Components\TextInput::make('region')
                ->label('Region')->default('VII')->maxLength(20),
            Forms\Components\TextInput::make('province')
                ->label('Province')->default('BOHOL')->maxLength(100),
            Forms\Components\TextInput::make('district')
                ->label('District')->maxLength(20),
            Forms\Components\TextInput::make('municipality')
                ->label('Municipality')->maxLength(100),
            Forms\Components\TextInput::make('barangay')
                ->label('Barangay')->maxLength(100),
            Forms\Components\TextInput::make('farmer_last_name')
                ->label('Family Name')->required()->maxLength(100),
            Forms\Components\TextInput::make('farmer_first_name')
                ->label('Given Name')->maxLength(100),
            Forms\Components\TextInput::make('farmer_middle_initial')
                ->label('M.I.')->maxLength(10),
            Forms\Components\Select::make('gender')
                ->label('Gender')
                ->options(['M' => 'Male', 'F' => 'Female']),
            Forms\Components\TextInput::make('farm_municipality')
                ->label('Farm Mun.')->maxLength(100),
            Forms\Components\TextInput::make('farm_barangay')
                ->label('Farm Brgy.')->maxLength(100),
            Forms\Components\TextInput::make('variety')
                ->label('Variety')->maxLength(100),
            Forms\Components\TextInput::make('seedlings_received')
                ->label('Received')->maxLength(50),
            Forms\Components\DatePicker::make('date_received')
                ->label('Date Recvd'),
            Forms\Components\TextInput::make('seedlings_planted')
                ->label('Qty Plntd')->numeric()->default(0),
            Forms\Components\DatePicker::make('date_planted')
                ->label('Date Planted'),
            Forms\Components\Textarea::make('remarks')
                ->placeholder('Remarks')->rows(2)->columnSpanFull(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Distribution Details')
                    ->icon('heroicon-o-truck')
                    ->description('Enter hybrid seedling distribution data')
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
                                        
                                        $latest = \App\Models\HybridDistribution::where('field_site_id', $siteId)
                                            ->orderBy('report_month', 'desc')
                                            ->first();
                                            
                                        if (!$latest) {
                                            \Filament\Notifications\Notification::make()->warning()->title('No previous records found for this site.')->send();
                                            return;
                                        }
                                        
                                        if ($latest->report_month) {
                                            $set('report_month', $latest->report_month->copy()->addMonth()->startOfMonth()->format('Y-m-d'));
                                        }
                                        $set('region', $latest->region);
                                        $set('province', $latest->province);
                                        $set('district', $latest->district);
                                        $set('municipality', $latest->municipality);
                                        $set('barangay', $latest->barangay);
                                        
                                        \Filament\Notifications\Notification::make()->success()->title('Loaded from previous record.')->send();
                                    }),
                            ]),
                        ])->columnSpan(1),

                        Forms\Components\DatePicker::make('report_month')
                            ->label('Report Month')
                            ->required()
                            ->displayFormat('m / d / Y')
                            ->default(now()->startOfMonth())
                            ->columnSpan(1),
                    ])->columns(2),

                // CREATE MODE: Repeater for batch-adding farmers (matching Django's "Add Farmer")
                Forms\Components\Section::make('Distribution List')
                    ->description('add multiple farmers here')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Forms\Components\Repeater::make('farmers')
                            ->label('')
                            ->schema(self::getFarmerFields())
                            ->columns(6)
                            ->addActionLabel('Add Farmer')
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                trim(($state['farmer_last_name'] ?? '') . ', ' . ($state['farmer_first_name'] ?? '')) ?: 'New Farmer entry'
                            ),
                    ])
                    ->hiddenOn('edit'),

                // EDIT MODE: Flat farmer-entry fields (single record)
                Forms\Components\Section::make('Farmer Entry')
                    ->icon('heroicon-o-user')
                    ->schema(self::getFarmerFields())
                    ->columns(6)
                    ->hiddenOn('create'),
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
                Tables\Columns\TextColumn::make('municipality')
                    ->searchable(),
                Tables\Columns\TextColumn::make('farmer_last_name')
                    ->label('Family Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('farmer_first_name')
                    ->label('Given Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('variety')
                    ->label('Type/Variety')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('seedlings_received')
                    ->label('Seedlings Received')
                    ->sortable(),
                Tables\Columns\TextColumn::make('seedlings_planted')
                    ->label('Seedlings Planted')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_received')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_planted')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                self::getStatusColumn(),
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
                Tables\Filters\Filter::make('report_month_filter')
                    ->form([
                        Forms\Components\Select::make('month')
                            ->options([
                                1 => 'January', 2 => 'February', 3 => 'March',
                                4 => 'April', 5 => 'May', 6 => 'June',
                                7 => 'July', 8 => 'August', 9 => 'September',
                                10 => 'October', 11 => 'November', 12 => 'December',
                            ]),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['month'] ?? null, fn ($q, $month) =>
                            $q->whereMonth('report_month', $month)
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
                    ->exporter(\App\Filament\Exports\HybridDistributionExporter::class),
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
                        $query = \App\Models\HybridDistribution::query();
                        
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
                        
                        $records = $query->with(['fieldSite'])->get();
                        
                        if ($records->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('No records found for the selected filters.')
                                ->send();
                            return;
                        }

                        $isCumulative = ($data['export_range'] ?? 'single') === 'cumulative';
                        return (new \App\Exports\HybridDistributionExport($records, $data['year'], $data['month'], $isCumulative))->export();
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Supervisors only see their field site data
        if (auth()->user()?->isSupervisor()) {
            $query->where('field_site_id', auth()->user()->field_site_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHybridDistributions::route('/'),
            'create' => Pages\CreateHybridDistribution::route('/create'),
            'edit' => Pages\EditHybridDistribution::route('/{record}/edit'),
        ];
    }
}
