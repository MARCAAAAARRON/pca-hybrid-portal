<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HybridizationRecordResource\Pages;
use App\Filament\Widgets\HybridizationOverview;
use App\Models\HybridizationRecord;
use App\Filament\Traits\HasApprovalActions;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HybridizationRecordResource extends Resource implements HasShieldPermissions
{
    use HasApprovalActions;
    protected static ?string $model = HybridizationRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Hybridization';

    protected static ?string $navigationLabel = 'Hybridization Records';

    protected static ?int $navigationSort = 1;

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Record Overview')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('hybrid_code')
                            ->label('Hybrid Identity')
                            ->weight('bold')
                            ->size('lg')
                            ->color('primary'),
                        \Filament\Infolists\Components\TextEntry::make('fieldSite.name')
                            ->label('Origin Site')
                            ->icon('heroicon-m-map-pin'),
                        \Filament\Infolists\Components\TextEntry::make('crop_type')
                            ->label('Classification'),
                        \Filament\Infolists\Components\Grid::make(3)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('parent_line_a')
                                    ->label('Male Parent (A)')
                                    ->badge()
                                    ->color('info'),
                                \Filament\Infolists\Components\TextEntry::make('parent_line_b')
                                    ->label('Female Parent (B)')
                                    ->badge()
                                    ->color('warning'),
                                \Filament\Infolists\Components\TextEntry::make('date_planted')
                                    ->date()
                                    ->label('Date Planted')
                                    ->icon('heroicon-m-calendar'),
                            ]),
                    ])->columns(2),

                \Filament\Infolists\Components\Section::make('Harvest & Growth Tracking')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('growth_status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'seedling' => 'gray',
                                'vegetative' => 'info',
                                'flowering' => 'warning',
                                'fruiting' => 'success',
                                'harvested' => 'primary',
                                default => 'gray',
                            }),
                        \Filament\Infolists\Components\TextEntry::make('estimated_harvest_date')
                            ->label('Target Harvest')
                            ->date('M Y')
                            ->color('success')
                            ->weight('bold'),
                        \Filament\Infolists\Components\TextEntry::make('days_until_harvest')
                            ->label('Timeline Status')
                            ->suffix(' Days')
                            ->badge()
                            ->color(fn ($record) => $record->days_until_harvest < 0 ? 'danger' : 'success'),
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->label('Validation State')
                            ->badge(),
                    ])->columns(4),

                \Filament\Infolists\Components\Section::make('Observations')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('notes')
                            ->placeholder('No field notes recorded.')
                            ->columnSpanFull(),
                        \Filament\Infolists\Components\TextEntry::make('admin_remarks')
                            ->placeholder('No admin remarks.')
                            ->visible(fn () => !auth()->user()?->isSupervisor())
                            ->columnSpanFull(),
                    ]),

                \Filament\Infolists\Components\Section::make('Field Evidence')
                    ->schema([
                        \Filament\Infolists\Components\SpatieMediaLibraryImageEntry::make('field_images')
                            ->collection('field_images')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),

                \Filament\Infolists\Components\Section::make('Audit Trail & Verification Timeline')
                    ->icon('heroicon-o-clock')
                    ->collapsible()
                    ->schema([
                        \Filament\Infolists\Components\ViewEntry::make('audit_timeline')
                            ->view('filament.infolists.audit-timeline')
                            ->columnSpanFull()
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Record Details')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\Select::make('field_site_id')
                            ->label('Field Site')
                            ->relationship('fieldSite', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->default(fn () => auth()->user()->field_site_id)
                            ->disabled(fn () => auth()->user()?->isSupervisor())
                            ->dehydrated(),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn () => auth()->id()),
                        Forms\Components\TextInput::make('crop_type')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('hybrid_code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                    ])->columns(3),

                Forms\Components\Section::make('Parent Lines')
                    ->icon('heroicon-o-arrows-right-left')
                    ->schema([
                        Forms\Components\TextInput::make('parent_line_a')
                            ->label('Parent Line A')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('parent_line_b')
                            ->label('Parent Line B')
                            ->required()
                            ->maxLength(100),
                    ])->columns(2),

                Forms\Components\Section::make('Growth & Status')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Forms\Components\DatePicker::make('date_planted')
                            ->required(),
                        Forms\Components\Select::make('growth_status')
                            ->options(HybridizationRecord::GROWTH_STATUS_CHOICES)
                            ->required()
                            ->default('seedling'),
                        Forms\Components\Select::make('status')
                            ->options(HybridizationRecord::STATUS_CHOICES)
                            ->required()
                            ->default('draft')
                            ->disabled(fn () => auth()->user()?->isSupervisor()),
                    ])->columns(3),

                Forms\Components\Section::make('Notes & Remarks')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('admin_remarks')
                            ->label('Admin Remarks')
                            ->rows(3)
                            ->visible(fn () => !auth()->user()?->isSupervisor())
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Field Images')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('field_images')
                            ->collection('field_images')
                            ->multiple()
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('hybrid_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('crop_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent_line_a')
                    ->label('Parent A')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('parent_line_b')
                    ->label('Parent B')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('date_planted')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimated_harvest')
                    ->label('Est. Harvest')
                    ->getStateUsing(fn ($record) => $record->estimated_harvest_date?->format('M Y') ?? '—')
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('success')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('days_left')
                    ->label('Countdown')
                    ->getStateUsing(function ($record) {
                        $days = $record->days_until_harvest;
                        if ($days === null) return '—';
                        if ($record->growth_status === 'harvested') return 'Done';
                        if ($days < 0) return abs($days) . 'd overdue';
                        if ($days === 0) return 'Today!';
                        return $days . 'd';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->growth_status === 'harvested') return 'primary';
                        $days = $record->days_until_harvest;
                        if ($days === null) return 'gray';
                        if ($days < 0) return 'danger';
                        if ($days <= 7) return 'danger';
                        if ($days <= 30) return 'warning';
                        return 'success';
                    })
                    ->alignment('center')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('growth_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'seedling' => 'gray',
                        'vegetative' => 'info',
                        'flowering' => 'warning',
                        'fruiting' => 'success',
                        'harvested' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'validated' => 'success',
                        'revision' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->toggleable(),
                self::getStatusColumn(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('field_site_id')
                    ->label('Field Site')
                    ->relationship('fieldSite', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(HybridizationRecord::STATUS_CHOICES),
                Tables\Filters\SelectFilter::make('growth_status')
                    ->options(HybridizationRecord::GROWTH_STATUS_CHOICES),
                Tables\Filters\Filter::make('harvest_readiness')
                    ->form([
                        Forms\Components\Select::make('readiness')
                            ->label('Harvest Readiness')
                            ->options([
                                'ready' => '🔴 Ready Now (overdue / ≤7 days)',
                                'upcoming' => '🟡 Upcoming (≤30 days)',
                                'growing' => '🟢 Still Growing (>30 days)',
                            ]),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        $readiness = $data['readiness'] ?? null;
                        if (! $readiness) return $query;

                        $months = '+' . HybridizationRecord::HARVEST_LEAD_MONTHS . ' months';

                        return match ($readiness) {
                            'ready' => $query
                                ->where('growth_status', '!=', 'harvested')
                                ->whereNotNull('date_planted')
                                ->whereRaw("date(date_planted, ?) <= ?", [$months, now()->addDays(7)->toDateString()]),
                            'upcoming' => $query
                                ->where('growth_status', '!=', 'harvested')
                                ->whereNotNull('date_planted')
                                ->whereRaw("date(date_planted, ?) BETWEEN ? AND ?", [$months, now()->toDateString(), now()->addDays(30)->toDateString()]),
                            'growing' => $query
                                ->where('growth_status', '!=', 'harvested')
                                ->whereNotNull('date_planted')
                                ->whereRaw("date(date_planted, ?) > ?", [$months, now()->addDays(30)->toDateString()]),
                            default => $query,
                        };
                    }),
                self::getStatusFilter(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                ...self::getApprovalActions(),
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
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(\App\Filament\Exports\HybridizationRecordExporter::class)
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
                            ->nullable(),
                        Forms\Components\Select::make('field_site_id')
                            ->label('Field Site')
                            ->relationship('fieldSite', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->hidden(fn () => auth()->user()?->isSupervisor())
                            ->default(fn () => auth()->user()?->isSupervisor() ? auth()->user()->field_site_id : null),
                    ])
                    ->modifyQueryUsing(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if ($data['year']) {
                            $query->whereYear('report_month', $data['year']);
                        }
                        if ($data['month']) {
                            $query->whereMonth('report_month', $data['month']);
                        }
                        if (auth()->user()?->isSupervisor()) {
                            $query->where('field_site_id', auth()->user()->field_site_id);
                        } elseif ($data['field_site_id']) {
                            $query->where('field_site_id', $data['field_site_id']);
                        }
                        return $query;
                    }),
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
            // HybridizationOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHybridizationRecords::route('/'),
            'create' => Pages\CreateHybridizationRecord::route('/create'),
            'edit' => Pages\EditHybridizationRecord::route('/{record}/edit'),
        ];
    }
}
