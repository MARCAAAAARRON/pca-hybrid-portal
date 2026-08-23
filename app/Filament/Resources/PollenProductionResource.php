<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PollenProductionResource\Pages;
use App\Models\PollenProduction;
use App\Filament\Traits\HasApprovalActions;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PollenProductionResource extends Resource implements HasShieldPermissions
{
    use HasApprovalActions;
    protected static ?string $model = PollenProduction::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Field Data';

    protected static ?string $navigationLabel = 'Pollen Production';

    protected static ?int $navigationSort = 6;

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

                Forms\Components\Section::make('Pollen Details')
                    ->icon('heroicon-o-beaker')
                    ->schema([
                        Forms\Components\Grid::make(4)->schema([
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
                                                
                                                $latest = \App\Models\PollenProduction::where('field_site_id', $siteId)
                                                    ->orderBy('report_month', 'desc')
                                                    ->first();
                                                    
                                                if (!$latest) {
                                                    \Filament\Notifications\Notification::make()->warning()->title('No previous records found for this site.')->send();
                                                    return;
                                                }
                                                
                                                if ($latest->report_month) {
                                                    $set('report_month', $latest->report_month->copy()->addMonth()->startOfMonth()->format('Y-m-d'));
                                                }
                                                $set('pollen_variety', $latest->pollen_variety);
                                                $set('ending_balance_prev', $latest->ending_balance);
                                                
                                                self::recalculatePollen($get, $set);
                                                
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
                                            $latest = \App\Models\PollenProduction::where('field_site_id', $siteId)
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
                                ->default(now()->startOfMonth()),

                            Forms\Components\Select::make('month_label')
                                ->label('Month Label')
                                ->options([
                                    'January' => 'January', 'February' => 'February', 'March' => 'March',
                                    'April' => 'April', 'May' => 'May', 'June' => 'June',
                                    'July' => 'July', 'August' => 'August', 'September' => 'September',
                                    'October' => 'October', 'November' => 'November', 'December' => 'December',
                                ])
                                ->placeholder('— Select Month —'),
                            Forms\Components\TextInput::make('pollen_variety')
                                ->label('Pollen Variety')
                                ->placeholder('e.g. LAGUNA TALL POLLENS')
                                ->maxLength(200),
                            Forms\Components\TextInput::make('ending_balance_prev')
                                ->label('Ending Balance (Last Month)')
                                ->numeric()
                                ->maxLength(50)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculatePollen($get, $set))
                                ->columnSpan(1),
                        ]),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Pollens Received from Other Center')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('pollen_source')
                                ->label('Source')
                                ->placeholder('e.g. CVSPC')
                                ->maxLength(200),
                            Forms\Components\DatePicker::make('date_received')
                                ->label('Date Received')
                                ->displayFormat('m / d / Y'),
                            Forms\Components\TextInput::make('pollens_received')
                                ->label('Amount of Pollens')
                                ->numeric()
                                ->maxLength(50)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculatePollen($get, $set)),
                        ]),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Pollen Utilization (grams per Week)')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Forms\Components\Grid::make(6)->schema([
                            Forms\Components\TextInput::make('week1')->label('Week 1')->numeric()->maxLength(20)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculatePollen($get, $set)),
                            Forms\Components\TextInput::make('week2')->label('Week 2')->numeric()->maxLength(20)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculatePollen($get, $set)),
                            Forms\Components\TextInput::make('week3')->label('Week 3')->numeric()->maxLength(20)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculatePollen($get, $set)),
                            Forms\Components\TextInput::make('week4')->label('Week 4')->numeric()->maxLength(20)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculatePollen($get, $set)),
                            Forms\Components\TextInput::make('week5')->label('Week 5')->numeric()->maxLength(20)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::recalculatePollen($get, $set)),
                            Forms\Components\TextInput::make('total_utilization')
                                ->label('Total Utilization')
                                ->numeric()
                                ->readOnly()
                                ->dehydrated()
                                ->helperText('Auto-computed: Sum of Week 1–5'),
                        ]),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Summary & Remarks')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Grid::make(1)->schema([
                                Forms\Components\TextInput::make('ending_balance')
                                    ->label('Ending Balance')
                                    ->numeric()
                                    ->readOnly()
                                    ->dehydrated()
                                    ->helperText('Auto-computed: Previous Balance + Received − Utilization'),
                            ])->columnSpan(1),
                            Forms\Components\Textarea::make('remarks')
                                ->label('Remarks')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columns(1),
            ]);
    }

    protected static function recalculatePollen(Forms\Get $get, Forms\Set $set): void
    {
        $weeks = collect(['week1', 'week2', 'week3', 'week4', 'week5'])
            ->map(fn ($field) => (float) ($get($field) ?? 0))
            ->sum();

        $set('total_utilization', $weeks);

        $prevBalance = (float) ($get('ending_balance_prev') ?? 0);
        $received = (float) ($get('pollens_received') ?? 0);

        $set('ending_balance', $prevBalance + $received - $weeks);
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
                Tables\Columns\TextColumn::make('pollen_variety')
                    ->label('Variety')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ending_balance')
                    ->label('Balance (g)')
                    ->weight('bold')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('viability_status')
                //     ->label('Viability')
                //     ->badge()
                //     ->color(fn (string $state): string => match ($state) {
                //         'fresh' => 'success',
                //         'at_risk' => 'warning',
                //         'expired' => 'danger',
                //         default => 'gray',
                //     })
                //     ->formatStateUsing(fn (string $state): string => match ($state) {
                //         'fresh' => 'Fresh (≤30d)',
                //         'at_risk' => 'At Risk (31–60d)',
                //         'expired' => 'Expired (>60d)',
                //         default => 'Unknown',
                //     }),
                // Tables\Columns\TextColumn::make('pollen_age_days')
                //     ->label('Age (Days)')
                //     ->numeric()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                self::getStatusColumn(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('field_site_id')
                    ->label('Field Site')
                    ->relationship('fieldSite', 'name'),
                // Tables\Filters\SelectFilter::make('viability')
                //     ->label('Viability Status')
                //     ->options([
                //         'fresh' => 'Fresh (≤30d)',
                //         'at_risk' => 'At Risk (31–60d)',
                //         'expired' => 'Expired (>60d)',
                //     ])
                //     ->query(function (Builder $query, array $data) {
                //         if (empty($data['value'])) return $query;
                //         return match ($data['value']) {
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
                        \Filament\Infolists\Components\TextEntry::make('pollen_variety')->label('Pollen Variety'),
                        \Filament\Infolists\Components\TextEntry::make('ending_balance')->label('Ending Balance (g)')->weight('bold'),
                        \Filament\Infolists\Components\TextEntry::make('status')->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'submitted' => 'warning',
                                'validated' => 'success',
                                'revision' => 'danger',
                                default => 'gray',
                            }),
                    ])->columns(5),

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
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
        if (auth()->user()?->isSupervisor()) {
            $query->where('field_site_id', auth()->user()->field_site_id);
        }
        return $query;
    }

    public static function getWidgets(): array
    {
        return [
            // \App\Filament\Resources\PollenProductionResource\Widgets\PollenStockWidget::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPollenProductions::route('/'),
            'create' => Pages\CreatePollenProduction::route('/create'),
            'edit' => Pages\EditPollenProduction::route('/{record}/edit'),
        ];
    }
}
