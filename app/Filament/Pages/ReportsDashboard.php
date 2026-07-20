<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Illuminate\Database\Eloquent\Builder;

class ReportsDashboard extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Field Data';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Reports';
    protected static ?string $title = 'Reports';

    protected static string $view = 'filament.pages.reports-dashboard';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['supervisor', 'manager', 'admin']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role, ['supervisor', 'manager', 'admin']);
    }

    public ?array $data = [];
    public $reportData = null; // Contains the queried records
    public $rawReportData = null; // Contains raw queried records for export
    public $reportFarms = null; // For aggregated data
    public bool $showModal = false; // Controls floating report modal
    public int $currentPage = 0; // Current page index for multi-site navigation
    public bool $batchMode = false; // Batch generation: fetch all field sites
    public array $siteNames = []; // Maps site_id => site_name for tab labels
    public array $siteIds = []; // Ordered list of site IDs for tab navigation

    // Multi-category mode (replaces old fullPackageMode)
    public bool $fullPackageMode = false;
    public array $fullPackageData = []; // [category => [site_id => {records, farms}]]
    public string $activeCategory = 'monthly_harvest'; // Active category tab
    public array $selectedCategories = []; // User-selected categories

    protected const CATEGORY_LABELS = [
        'monthly_harvest'    => 'Monthly Harvest',
        'pollen_production'  => 'Pollen Production',
        'hybrid_distribution'=> 'Hybrid Distribution',
        'nursery_operation'  => 'Nursery Operations',
        'terminal_report'    => 'Terminal Reports',
    ];

    protected const CATEGORY_SHORT_LABELS = [
        'monthly_harvest'    => 'Monthly Harvest',
        'pollen_production'  => 'Pollen Prod.',
        'hybrid_distribution'=> 'Hybrid Dist.',
        'nursery_operation'  => 'Nursery Ops.',
        'terminal_report'    => 'Terminal',
    ];

    protected const CATEGORY_ICONS = [
        'monthly_harvest'    => 'heroicon-o-sun',
        'pollen_production'  => 'heroicon-o-sparkles',
        'hybrid_distribution'=> 'heroicon-o-arrows-right-left',
        'nursery_operation'  => 'heroicon-o-beaker',
        'terminal_report'    => 'heroicon-o-clipboard-document-list',
    ];

    public function openReportModal(): void
    {
        $this->showModal = true;
    }

    public function firstPage(): void
    {
        $this->currentPage = 0;
    }

    public function lastPage(): void
    {
        $this->currentPage = count($this->reportData) - 1;
    }

    public function goToPage(int $index): void
    {
        if ($index >= 0 && $index < count((array) $this->reportData)) {
            $this->currentPage = $index;
        }
    }

    public function switchCategory(string $category): void
    {
        if (isset($this->fullPackageData[$category])) {
            $this->activeCategory = $category;
            // Sync reportData to the selected category's data
            $this->reportData = $this->fullPackageData[$category];
            $siteIdList = array_keys($this->fullPackageData[$category]);
            $this->siteIds = $siteIdList;
            $this->currentPage = 0;
        }
    }

    /**
     * Build an Eloquent query for a given report category.
     */
    protected function buildCategoryQuery(string $category): ?Builder
    {
        return match ($category) {
            'monthly_harvest' => \App\Models\MonthlyHarvest::query()->with(['fieldSite', 'varieties']),
            'pollen_production' => \App\Models\PollenProduction::query()->with(['fieldSite']),
            'hybrid_distribution' => \App\Models\HybridDistribution::query()->with(['fieldSite']),
            'nursery_operation' => \App\Models\NurseryOperation::query()->where('report_type', 'operation')->with(['fieldSite', 'batches.varieties']),
            'terminal_report' => \App\Models\NurseryOperation::query()->where('report_type', 'terminal')->with(['fieldSite', 'batches.varieties']),
            default => null,
        };
    }

    /**
     * Apply common date and site filters to a query.
     */
    protected function applyFilters(Builder $query, string $category, array $data): Builder
    {
        $query->whereYear('report_month', $data['year']);

        // Selective month filtering: Apply month ONLY to monthly reports
        if (!empty($data['month']) && in_array($category, ['monthly_harvest', 'pollen_production', 'hybrid_distribution'])) {
            if (($data['export_range'] ?? 'single') === 'cumulative') {
                $query->whereMonth('report_month', '<=', $data['month']);
            } else {
                $query->whereMonth('report_month', $data['month']);
            }
        }

        if (auth()->user()?->isSupervisor()) {
            $query->where('field_site_id', auth()->user()->field_site_id);
        } elseif (!$this->batchMode && !empty($data['field_site_id'])) {
            $query->where('field_site_id', $data['field_site_id']);
        }

        return $query;
    }

    #[\Livewire\Attributes\Url]
    public ?string $category = null;

    public function mount(): void
    {
        $latestMonth = null;

        // Auto-detect the latest record month for the selected category (from URL)
        if ($this->category) {
            $modelClass = match ($this->category) {
                'monthly_harvest' => \App\Models\MonthlyHarvest::class,
                'pollen_production' => \App\Models\PollenProduction::class,
                'hybrid_distribution' => \App\Models\HybridDistribution::class,
                'nursery_operation' => \App\Models\NurseryOperation::class,
                'terminal_report' => \App\Models\NurseryOperation::class,
                default => null,
            };

            if ($modelClass) {
                $query = $modelClass::query()->whereYear('report_month', now()->year);

                // Scope to user's field site if supervisor
                if (auth()->user()?->isSupervisor()) {
                    $query->where('field_site_id', auth()->user()->field_site_id);
                }

                // For nursery vs terminal, filter by report_type
                if ($this->category === 'nursery_operation') {
                    $query->where('report_type', 'operation');
                } elseif ($this->category === 'terminal_report') {
                    $query->where('report_type', 'terminal');
                }

                $latestRecord = $query->orderByDesc('report_month')->first();
                if ($latestRecord) {
                    $latestMonth = \Carbon\Carbon::parse($latestRecord->report_month)->month;
                }
            }
        }

        $reqYear = request()->query('year');
        $reqMonth = request()->query('month');
        $reqSiteId = request()->query('field_site_id');

        // Convert single URL category to array for the CheckboxList
        $initialCategories = [];
        if ($this->category) {
            $initialCategories = [$this->category];
        }

        $this->form->fill([
            'categories' => $initialCategories,
            'year' => $reqYear ?? now()->year,
            'month' => $reqMonth ?? $latestMonth,
            'export_range' => (($reqMonth ?? $latestMonth) && ($reqMonth ?? $latestMonth) > 1) ? 'cumulative' : 'single',
            'field_site_id' => $reqSiteId ?? (auth()->user()?->isSupervisor() ? auth()->user()->field_site_id : null),
        ]);

        if ($this->category) {
            $this->selectedCategories = $initialCategories;
            $this->generateReport();
        }
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Components\Section::make('Report Filters')
                ->extraAttributes(['style' => 'overflow: visible'])
                ->schema([
                    Components\CheckboxList::make('categories')
                        ->label('Report Categories')
                        ->options([
                            'monthly_harvest' => 'Monthly Harvest',
                            'pollen_production' => 'Pollen Production',
                            'hybrid_distribution' => 'Hybrid Distribution',
                            'nursery_operation' => 'Nursery Operations',
                            'terminal_report' => 'Terminal Reports',
                        ])
                        ->descriptions([
                            'monthly_harvest' => 'Seednut production by farm partner',
                            'pollen_production' => 'Pollen utilization & stock levels',
                            'hybrid_distribution' => 'Seedling distribution to farmers',
                            'nursery_operation' => 'Monthly nursery batch operations',
                            'terminal_report' => 'Terminal nursery operation reports',
                        ])
                        ->columns(3)
                        ->bulkToggleable()
                        ->live()
                        ->columnSpanFull(),
                    Components\Select::make('year')
                        ->options(fn() => collect(range(now()->year, 2024, -1))->mapWithKeys(fn($y) => [$y => $y]))
                        ->required(),
                    Components\Select::make('month')
                        ->options([
                            1 => 'January',
                            2 => 'February',
                            3 => 'March',
                            4 => 'April',
                            5 => 'May',
                            6 => 'June',
                            7 => 'July',
                            8 => 'August',
                            9 => 'September',
                            10 => 'October',
                            11 => 'November',
                            12 => 'December',
                        ])
                        ->nullable()
                        ->live()
                        ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                            if ($state == 1) {
                                $set('export_range', 'single');
                            }
                        }),
                    Components\Radio::make('export_range')
                        ->label('Export Coverage')
                        ->options([
                            'single' => 'Selected Month Only',
                            'cumulative' => 'Cumulative (Jan to Selected Month)',
                        ])
                        ->inline()
                        ->disableOptionWhen(fn(string $value, \Filament\Forms\Get $get) => $value === 'cumulative' && $get('month') == 1)
                        ->visible(fn(\Filament\Forms\Get $get) => filled($get('month'))),
                    Components\Select::make('field_site_id')
                        ->label('Field Site')
                        ->options(\App\Models\FieldSite::pluck('name', 'id'))
                        ->nullable()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull()
                        ->hidden(fn(\Filament\Forms\Get $get) => auth()->user()?->isSupervisor() || $get('batch_mode')),
                    Components\Toggle::make('batch_mode')
                        ->label('Include All Field Sites')
                        ->helperText('Fetches records from every field site for the selected categories.')
                        ->columnSpanFull()
                        ->live()
                        ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                            if ($state) {
                                $set('field_site_id', null);
                            }
                            $this->batchMode = (bool) $state;
                        })
                        ->visible(fn(\Filament\Forms\Get $get) => !auth()->user()?->isSupervisor()),
                ])->columns(2),
        ])->statePath('data');
    }

    /**
     * Unified report generation — handles 1 or more selected categories.
     */
    public function generateReport()
    {
        $data = $this->form->getState();
        $selectedCats = $data['categories'] ?? [];

        if (empty($selectedCats)) {
            \Filament\Notifications\Notification::make()->danger()->title('Please select at least one report category.')->send();
            return;
        }

        $this->selectedCategories = $selectedCats;

        if (count($selectedCats) === 1) {
            $this->generateSingleCategoryReport($selectedCats[0], $data);
        } else {
            $this->generateMultiCategoryReport($selectedCats, $data);
        }
    }

    /**
     * Generate a report for a single selected category (no category tabs).
     */
    protected function generateSingleCategoryReport(string $cat, array $data): void
    {
        $this->fullPackageMode = false;
        $this->fullPackageData = [];

        $query = $this->buildCategoryQuery($cat);
        if (!$query) return;

        $this->applyFilters($query, $cat, $data);

        $records = $query->get();

        if ($records->isEmpty()) {
            \Filament\Notifications\Notification::make()->warning()->title('No records found for the selected filters.')->send();
            $this->reportData = null;
            $this->reportFarms = null;
            $this->siteNames = [];
            $this->siteIds = [];
            return;
        }

        $this->rawReportData = $records;
        $this->reportData = [];

        $grouped = $records->groupBy('field_site_id');

        // Build the named site map for tab navigation
        $siteIdList = $grouped->keys()->toArray();
        $this->siteIds = $siteIdList;
        $this->siteNames = \App\Models\FieldSite::whereIn('id', $siteIdList)
            ->pluck('name', 'id')
            ->toArray();

        foreach ($grouped as $siteId => $siteRecords) {
            $this->reportData[$siteId] = [
                'records' => $siteRecords,
                'farms' => $cat === 'monthly_harvest' ? $this->groupHarvestData($siteRecords) : null,
            ];
        }

        // Set active category and open the modal
        $this->activeCategory = $cat;
        $this->currentPage = 0;
        $this->showModal = true;
    }

    /**
     * Generate reports for multiple selected categories (with category tabs).
     */
    protected function generateMultiCategoryReport(array $selectedCats, array $data): void
    {
        $this->fullPackageData = [];
        $allSiteIds = [];

        foreach ($selectedCats as $cat) {
            $query = $this->buildCategoryQuery($cat);
            if (!$query) continue;

            $this->applyFilters($query, $cat, $data);

            $records = $query->get();
            $grouped = $records->groupBy('field_site_id');

            $catData = [];
            foreach ($grouped as $siteId => $siteRecords) {
                $catData[$siteId] = [
                    'records' => $siteRecords,
                    'farms'   => $cat === 'monthly_harvest' ? $this->groupHarvestData($siteRecords) : null,
                ];
                $allSiteIds[] = $siteId;
            }

            $this->fullPackageData[$cat] = $catData;
        }

        // Build unified site name map
        $uniqueSiteIds = array_unique($allSiteIds);
        $this->siteNames = \App\Models\FieldSite::whereIn('id', $uniqueSiteIds)
            ->pluck('name', 'id')->toArray();

        if (empty(array_filter($this->fullPackageData))) {
            \Filament\Notifications\Notification::make()->warning()->title('No records found for the selected period.')->send();
            return;
        }

        // Start on first selected category that has data
        $this->activeCategory = $selectedCats[0];
        foreach ($selectedCats as $cat) {
            if (!empty($this->fullPackageData[$cat])) {
                $this->activeCategory = $cat;
                break;
            }
        }

        // Sync reportData to the first active category
        $this->reportData = $this->fullPackageData[$this->activeCategory] ?? [];
        $siteIdList = array_keys($this->reportData);
        $this->siteIds = $siteIdList;
        $this->currentPage = 0;
        $this->fullPackageMode = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function nextPage(): void
    {
        if ($this->reportData && $this->currentPage < count($this->reportData) - 1) {
            $this->currentPage++;
        }
    }

    public function prevPage(): void
    {
        if ($this->currentPage > 0) {
            $this->currentPage--;
        }
    }

    public function getTotalPagesProperty(): int
    {
        return $this->reportData ? count($this->reportData) : 0;
    }

    protected function groupHarvestData($records)
    {
        $farms = [];
        foreach ($records as $rec) {
            $key = ($rec->location ?? '') . '|' . ($rec->farm_name ?? '');
            if (!isset($farms[$key])) {
                $farms[$key] = [
                    'location' => $rec->location,
                    'farm_name' => $rec->farm_name,
                    'area_ha' => $rec->area_ha,
                    'age_of_palms' => $rec->age_of_palms,
                    'num_hybridized_palms' => $rec->num_hybridized_palms,
                    'num_palms' => $rec->num_palms ?? 0, // For Pollen
                    'varieties' => [],
                ];
            }

            $relations = $rec->varieties;

            foreach ($relations as $v) {
                $varietyValue = $v->variety ?? '';
                $typeValue = $v->seednuts_type ?? '';
                $varKey = $varietyValue . '|' . $typeValue;

                if (!isset($farms[$key]['varieties'][$varKey])) {
                    $farms[$key]['varieties'][$varKey] = [
                        'variety' => $varietyValue,
                        'type' => $typeValue,
                        'months' => array_fill(1, 12, 0),
                        'remarks' => $v->remarks,
                    ];
                }
                $month = \Carbon\Carbon::parse($rec->report_month)->month;
                $farms[$key]['varieties'][$varKey]['months'][$month] += ($v->seednuts_count ?? 0);
            }
        }
        return $farms;
    }

    public function shareAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('share')
            ->label('Share via Email')
            ->icon('heroicon-o-envelope')
            ->color('success')
            ->visible(fn() => in_array(auth()->user()?->role, ['manager', 'admin']))
            ->color('success')
            ->form([
                \Filament\Forms\Components\Section::make('Email Details')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->label('Recipient Email')
                            ->placeholder('stakeholder@pca.gov.ph'),
                    ]),
                \Filament\Forms\Components\Section::make('PDF Settings')
                    ->description('Customize the layout of the attached PDF report.')
                    ->schema([
                        \Filament\Forms\Components\Radio::make('orientation')
                            ->label('Page Orientation')
                            ->options([
                                'landscape' => 'Landscape',
                                'portrait' => 'Portrait',
                            ])
                            ->default('landscape')
                            ->inline()
                            ->required(),
                        \Filament\Forms\Components\Select::make('paper_size')
                            ->label('Paper Size')
                            ->options([
                                'legal' => 'Legal (8.5" × 14")',
                                'a4' => 'A4 (210 × 297 mm)',
                                'letter' => 'Letter (8.5" × 11")',
                                'folio' => 'Folio (8.5" × 13")',
                            ])
                            ->default('legal')
                            ->required()
                            ->native(false),
                    ]),
            ])
            ->action(function (array $data) {
                set_time_limit(120);
                $formData = $this->form->getState();
                $isCumulative = ($formData['export_range'] ?? 'single') === 'cumulative';
                
                $unnotedRecords = collect();
                $exporter = null;
                $filename = 'Report.xlsx';

                if ($this->fullPackageMode) {
                    foreach ($this->fullPackageData as $cat => $sites) {
                        foreach ($sites as $siteId => $siteData) {
                            if (isset($siteData['records'])) {
                                $unnotedRecords = $unnotedRecords->merge($siteData['records']->where('status', '!=', 'noted'));
                            }
                        }
                    }
                    
                    if ($unnotedRecords->isEmpty()) {
                        $exporter = new \App\Exports\FullPackageExport($this->fullPackageData, $formData['year'], $formData['month'], $isCumulative);
                        $period = $formData['month'] ? \Carbon\Carbon::create($formData['year'], $formData['month'], 1)->format('F_Y') : $formData['year'];
                        $filename = 'Full_Report_Package_' . $period . '.xlsx';
                    }
                } else {
                    // Single category — re-apply filters to validate
                    $activeCat = $this->activeCategory;
                    $query = $this->buildCategoryQuery($activeCat);

                    if (!$query) return;

                    $this->applyFilters($query, $activeCat, $formData);

                    $activeRecords = $query->get();

                    if ($activeRecords->isEmpty()) {
                        \Filament\Notifications\Notification::make()->danger()->title('No data to export.')->send();
                        return;
                    }

                    $unnotedRecords = $activeRecords->where('status', '!=', 'noted');

                    if ($unnotedRecords->isEmpty()) {
                        switch ($activeCat) {
                            case 'monthly_harvest':
                                $exporter = new \App\Exports\MonthlyHarvestExport($activeRecords, $formData['year'], $formData['month'], $isCumulative);
                                $filename = 'Monthly_Harvest.xlsx';
                                break;
                            case 'pollen_production':
                                $exporter = new \App\Exports\PollenProductionExport($activeRecords, $formData['year'], $formData['month'], $isCumulative);
                                $filename = 'Pollen_Production.xlsx';
                                break;
                            case 'hybrid_distribution':
                                $exporter = new \App\Exports\HybridDistributionExport($activeRecords, $formData['year'], $formData['month'], $isCumulative);
                                $filename = 'Hybrid_Distribution.xlsx';
                                break;
                            case 'nursery_operation':
                                $exporter = new \App\Exports\NurseryOperationExport($activeRecords, $formData['year'], $formData['month'], $isCumulative);
                                $filename = 'Nursery_Operation.xlsx';
                                break;
                            case 'terminal_report':
                                $exporter = new \App\Exports\NurseryOperationExport($activeRecords, $formData['year'], $formData['month'], $isCumulative);
                                $filename = 'Terminal_Report.xlsx';
                                break;
                        }
                    }
                }

                $unnotedCount = $unnotedRecords->count();
                
                if ($unnotedCount > 0) {
                    $unnotedIds = $unnotedRecords->pluck('id')->implode(', ');
                    \Filament\Notifications\Notification::make()
                        ->danger()
                        ->title('Action Denied')
                        ->body("Cannot share via email. There are {$unnotedCount} record(s) in this selection (IDs: {$unnotedIds}) that have not completed the full approval workflow. Ensure they are all in 'Noted' status.")
                        ->send();
                    return;
                }

                if ($exporter) {
                    try {
                        // Generate Excel
                        $response = $exporter->export();
                        $excelFile = $response->getFile()->getPathname();
                        
                        // Generate PDF
                        $pdfInfo = $this->generatePdfReport($data['orientation'], $data['paper_size'], $formData);
                        $pdfFile = $pdfInfo['path'];
                        $pdfName = $pdfInfo['filename'];

                        // Attach Both
                        $filesToAttach = [$excelFile, $pdfFile];
                        $fileNames = [$filename, $pdfName];

                        \Illuminate\Support\Facades\Mail::to($data['email'])
                            ->send(new \App\Mail\FieldDataReportMail($filesToAttach, $fileNames));

                        $notification = \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Report Shared via Email')
                            ->body('The PDF and Excel reports have been successfully emailed to ' . $data['email']);

                        $notification->send();
                        $notification->sendToDatabase(auth()->user());

                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()->danger()->title('Error generating export: ' . $e->getMessage())->send();
                    }
                }
            });
    }

    public function exportExcelAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('exportExcel')
            ->label('Export to Excel')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->action(function () {
                $data = $this->form->getState();
                $isCumulative = ($data['export_range'] ?? 'single') === 'cumulative';
                $exporter = null;

                if ($this->fullPackageMode) {
                    $exporter = new \App\Exports\FullPackageExport($this->fullPackageData, $data['year'], $data['month'], $isCumulative);
                } else {
                    if (!$this->rawReportData || $this->rawReportData->isEmpty()) {
                        \Filament\Notifications\Notification::make()->danger()->title('No data to export.')->send();
                        return;
                    }

                    $activeCat = $this->activeCategory;
                    switch ($activeCat) {
                        case 'monthly_harvest':
                            $exporter = new \App\Exports\MonthlyHarvestExport($this->rawReportData, $data['year'], $data['month'], $isCumulative);
                            break;
                        case 'pollen_production':
                            $exporter = new \App\Exports\PollenProductionExport($this->rawReportData, $data['year'], $data['month'], $isCumulative);
                            break;
                        case 'hybrid_distribution':
                            $exporter = new \App\Exports\HybridDistributionExport($this->rawReportData, $data['year'], $data['month'], $isCumulative);
                            break;
                        case 'nursery_operation':
                            $exporter = new \App\Exports\NurseryOperationExport($this->rawReportData, $data['year'], $data['month'], $isCumulative);
                            break;
                        case 'terminal_report':
                            $exporter = new \App\Exports\NurseryOperationExport($this->rawReportData, $data['year'], $data['month'], $isCumulative);
                            break;
                    }
                }

                if ($exporter && method_exists($exporter, 'export')) {
                    return $exporter->export();
                }
            });
    }

    public function exportPdfAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('exportPdf')
            ->label('Export PDF')
            ->icon('heroicon-o-document-arrow-down')
            ->color('danger')
            ->size('sm')
            ->form([
                \Filament\Forms\Components\Radio::make('orientation')
                    ->label('Page Orientation')
                    ->options([
                        'landscape' => 'Landscape',
                        'portrait' => 'Portrait',
                    ])
                    ->default('landscape')
                    ->inline()
                    ->required(),
                \Filament\Forms\Components\Select::make('paper_size')
                    ->label('Paper Size')
                    ->options([
                        'legal' => 'Legal (8.5" × 14")',
                        'a4' => 'A4 (210 × 297 mm)',
                        'letter' => 'Letter (8.5" × 11")',
                        'folio' => 'Folio (8.5" × 13")',
                    ])
                    ->default('legal')
                    ->required()
                    ->native(false),
            ])
            ->modalHeading('Export as PDF')
            ->modalDescription('Select the paper orientation and size for your PDF report.')
            ->modalSubmitActionLabel('Generate PDF')
            ->modalIcon('heroicon-o-document-arrow-down')
            ->action(function (array $data) {
                set_time_limit(120);
                ini_set('memory_limit', '512M');

                try {
                    $formData = $this->form->getState();
                    
                    $pdfInfo = $this->generatePdfReport($data['orientation'], $data['paper_size'], $formData);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('PDF Generated Successfully')
                        ->body("Report saved and download started.")
                        ->send();

                    return response()->streamDownload(
                        fn () => print($pdfInfo['content']),
                        $pdfInfo['filename'],
                        ['Content-Type' => 'application/pdf']
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('PDF Export Error: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->danger()
                        ->title('PDF Generation Failed')
                        ->body('Error: ' . \Illuminate\Support\Str::limit($e->getMessage(), 120))
                        ->persistent()
                        ->send();
                }
            });
    }
    protected function generatePdfReport($orientation, $paperSize, $formData)
    {
        $isCumulative = ($formData['export_range'] ?? 'single') === 'cumulative';

        $asOfDate = \Carbon\Carbon::create($formData['year'], $formData['month'] ?: 1, 1);
        $activeCat = $this->activeCategory;

        if ($formData['year'] && empty($formData['month'])) {
            $periodStr = in_array($activeCat, ['hybrid_distribution', 'nursery_operation', 'terminal_report'])
                ? 'as of end of ' . $formData['year']
                : 'For the year ' . $formData['year'];
        } elseif ($isCumulative) {
            $periodStr = in_array($activeCat, ['hybrid_distribution', 'nursery_operation', 'terminal_report'])
                ? 'Cumulative as of ' . $asOfDate->endOfMonth()->format('F d, Y')
                : 'For the months of January to ' . $asOfDate->format('F Y');
        } else {
            $periodStr = in_array($activeCat, ['hybrid_distribution', 'nursery_operation', 'terminal_report'])
                ? 'as of ' . $asOfDate->endOfMonth()->format('F d, Y')
                : 'For the month of ' . $asOfDate->format('F Y');
        }

        $pages = [];

        if ($this->fullPackageMode) {
            foreach ($this->selectedCategories as $cat) {
                $catData = $this->fullPackageData[$cat] ?? [];
                foreach ($catData as $siteId => $siteData) {
                    $pages[] = [
                        'category' => $cat,
                        'records'  => $siteData['records'],
                        'farms'    => $siteData['farms'] ?? null,
                    ];
                }
            }
        } else {
            if (!empty($this->reportData)) {
                foreach ($this->reportData as $siteId => $siteData) {
                    $pages[] = [
                        'category' => $activeCat,
                        'records'  => $siteData['records'],
                        'farms'    => $siteData['farms'] ?? null,
                    ];
                }
            }
        }

        if (empty($pages)) {
            throw new \Exception('No data available for PDF export.');
        }

        $categoryLabels = [
            'monthly_harvest'     => 'Monthly Harvest',
            'pollen_production'   => 'Pollen Production',
            'hybrid_distribution' => 'Hybrid Distribution',
            'nursery_operation'   => 'Nursery Operation',
            'terminal_report'     => 'Terminal Report',
        ];

        if ($this->fullPackageMode) {
            $catNames = collect($this->selectedCategories)->map(fn($c) => $categoryLabels[$c] ?? $c)->implode(' + ');
            $reportTitle = "Report Package ({$catNames})";
        } else {
            $reportTitle = $categoryLabels[$activeCat] ?? $activeCat;
        }

        $firstSite = $pages[0]['records']->first()->fieldSite?->name ?? 'All Sites';
        $period = $formData['month']
            ? $asOfDate->format('F_Y')
            : $formData['year'];
        $pdfTitle = "{$reportTitle} - {$firstSite} - {$period}";

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report-dashboard', [
            'pages'       => $pages,
            'periodStr'   => $periodStr,
            'title'       => $pdfTitle,
            'filterMonth' => $formData['month'] ?? null,
            'filterYear'  => $formData['year'] ?? null,
        ])->setPaper($paperSize, $orientation);

        $pdfContent = $pdf->output();

        $safeSiteName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $firstSite);
        $safeCatName = $this->fullPackageMode ? 'Full_Package' : preg_replace('/[^A-Za-z0-9_\-]/', '_', $categoryLabels[$activeCat] ?? $activeCat);
        $timestamp = now()->format('Ymd_His');
        $filename = "{$safeCatName}_{$safeSiteName}_{$period}_{$timestamp}.pdf";

        $storagePath = "reports/{$filename}";
        \Illuminate\Support\Facades\Storage::disk('public')->put($storagePath, $pdfContent);

        $fieldSiteId = null;
        if (!$this->fullPackageMode && count($this->reportData) === 1) {
            $fieldSiteId = array_key_first($this->reportData);
        } elseif (auth()->user()?->isSupervisor()) {
            $fieldSiteId = auth()->user()->field_site_id;
        }

        \App\Models\Report::create([
            'generated_by'  => auth()->id(),
            'report_type'   => 'pdf',
            'field_site_id' => $fieldSiteId,
            'title'         => $pdfTitle,
            'file'          => $storagePath,
        ]);

        return [
            'content'  => $pdfContent,
            'filename' => $filename,
            'path'     => \Illuminate\Support\Facades\Storage::disk('public')->path($storagePath),
        ];
    }
}
