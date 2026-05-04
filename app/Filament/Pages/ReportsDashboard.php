<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Illuminate\Database\Eloquent\Builder;

class ReportsDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Field Data';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Reports';
    protected static ?string $title = 'Reports';

    protected static string $view = 'filament.pages.reports-dashboard';

    public ?array $data = [];
    public $reportData = null; // Contains the queried records
    public $rawReportData = null; // Contains raw queried records for export
    public $reportFarms = null; // For aggregated data

    #[\Livewire\Attributes\Url]
    public ?string $category = null;

    public function mount(): void
    {
        $latestMonth = null;

        // Auto-detect the latest record month for the selected category
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

        $this->form->fill([
            'category' => request()->query('category', $this->category),
            'year' => $reqYear ?? now()->year,
            'month' => $reqMonth ?? $latestMonth,
            'export_range' => (($reqMonth ?? $latestMonth) && ($reqMonth ?? $latestMonth) > 1) ? 'cumulative' : 'single',
            'field_site_id' => $reqSiteId ?? (auth()->user()?->isSupervisor() ? auth()->user()->field_site_id : null),
        ]);
        
        if ($this->category) {
            $this->generateReport();
        }
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Components\Section::make('Report Filters')->schema([
                Components\Select::make('category')
                    ->label('Report Category')
                    ->options([
                        'monthly_harvest' => 'Monthly Harvest',
                        'pollen_production' => 'Pollen Production',
                        'hybrid_distribution' => 'Hybrid Distribution',
                        'nursery_operation' => 'Nursery Operations',
                        'terminal_report' => 'Terminal Reports',
                    ])
                    ->required()
                    ->live(),
                Components\Select::make('year')
                    ->options(fn () => collect(range(now()->year, 2024, -1))->mapWithKeys(fn ($y) => [$y => $y]))
                    ->required(),
                Components\Select::make('month')
                    ->options([
                        1 => 'January', 2 => 'February', 3 => 'March',
                        4 => 'April', 5 => 'May', 6 => 'June',
                        7 => 'July', 8 => 'August', 9 => 'September',
                        10 => 'October', 11 => 'November', 12 => 'December',
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
                    ->disableOptionWhen(fn (string $value, \Filament\Forms\Get $get) => $value === 'cumulative' && $get('month') == 1)
                    ->visible(fn (\Filament\Forms\Get $get) => filled($get('month'))),
                Components\Select::make('field_site_id')
                    ->label('Field Site')
                    ->options(\App\Models\FieldSite::pluck('name', 'id'))
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->hidden(fn () => auth()->user()?->isSupervisor()),
            ])->columns(3),
        ])->statePath('data');
    }

    public function generateReport()
    {
        $data = $this->form->getState();
        $query = null;

        switch ($data['category']) {
            case 'monthly_harvest':
                $query = \App\Models\MonthlyHarvest::query()->with(['fieldSite', 'varieties']);
                break;
            case 'pollen_production':
                $query = \App\Models\PollenProduction::query()->with(['fieldSite']);
                break;
            case 'hybrid_distribution':
                $query = \App\Models\HybridDistribution::query()->with(['fieldSite']);
                break;
            case 'nursery_operation':
                $query = \App\Models\NurseryOperation::query()->where('report_type', 'operation')->with(['fieldSite', 'batches.varieties']);
                break;
            case 'terminal_report':
                $query = \App\Models\NurseryOperation::query()->where('report_type', 'terminal')->with(['fieldSite', 'batches.varieties']);
                break;
        }

        if (!$query) return;

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
        } elseif (!empty($data['field_site_id'])) {
            $query->where('field_site_id', $data['field_site_id']);
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            \Filament\Notifications\Notification::make()->warning()->title('No records found for the selected filters.')->send();
            $this->reportData = null;
            $this->reportFarms = null;
            return;
        }

        $this->rawReportData = $records;
        $this->reportData = [];

        $grouped = $records->groupBy('field_site_id');
        
        foreach ($grouped as $siteId => $siteRecords) {
            $siteData = [
                'records' => $siteRecords,
                'farms' => null,
            ];
            
            // Group data based on category
            if ($data['category'] === 'monthly_harvest') {
                $siteData['farms'] = $this->groupHarvestData($siteRecords);
            }
            
            $this->reportData[$siteId] = $siteData;
        }
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
            ->visible(fn () => in_array(auth()->user()?->role, ['manager', 'admin', 'superadmin']))
            ->color('success')
            ->form([
                \Filament\Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->label('Recipient Email')
                    ->placeholder('stakeholder@pca.gov.ph'),
            ])
            ->action(function (array $data) {
                set_time_limit(120);
                $formData = $this->form->getState();
                if (!$this->rawReportData || $this->rawReportData->isEmpty()) {
                    \Filament\Notifications\Notification::make()->danger()->title('No data to export.')->send();
                    return;
                }

                // Business logic: Ensure all records are fully approved (noted)
                $unnotedCount = $this->rawReportData->where('status', '!=', 'noted')->count();
                if ($unnotedCount > 0) {
                    \Filament\Notifications\Notification::make()
                        ->danger()
                        ->title('Action Denied')
                        ->body("Cannot share via email. There are {$unnotedCount} record(s) in this report that have not completed the full approval workflow (Prepared, Reviewed, and Noted).")
                        ->send();
                    return;
                }

                $exporter = null;
                $isCumulative = ($formData['export_range'] ?? 'single') === 'cumulative';
                
                switch ($formData['category']) {
                    case 'monthly_harvest':
                        $exporter = new \App\Exports\MonthlyHarvestExport($this->rawReportData, $formData['year'], $formData['month'], $isCumulative);
                        $filename = 'Monthly_Harvest.xlsx';
                        break;
                    case 'pollen_production':
                        $exporter = new \App\Exports\PollenProductionExport($this->rawReportData, $formData['year'], $formData['month'], $isCumulative);
                        $filename = 'Pollen_Production.xlsx';
                        break;
                    case 'hybrid_distribution':
                        $exporter = new \App\Exports\HybridDistributionExport($this->rawReportData, $formData['year'], $formData['month'], $isCumulative);
                        $filename = 'Hybrid_Distribution.xlsx';
                        break;
                    case 'nursery_operation':
                        $exporter = new \App\Exports\NurseryOperationExport($this->rawReportData, $formData['year'], $formData['month'], $isCumulative);
                        $filename = 'Nursery_Operation.xlsx';
                        break;
                    case 'terminal_report':
                        $exporter = new \App\Exports\NurseryOperationExport($this->rawReportData, $formData['year'], $formData['month'], $isCumulative);
                        $filename = 'Terminal_Report.xlsx';
                        break;
                }

                if ($exporter) {
                    try {
                        // We must invoke export() and intercept the file, but export() returns a Download Response.
                        // So we instantiate the spreadsheet and writer manually just like export() does.
                        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                        $spreadsheet->removeSheetByIndex(0);
                        
                        // We will just do a simpler manual save based on their code, or call export if possible.
                        // Wait, their export() groups by FieldSite internally. 
                        // It's much easier to just reflect on it or save manually.
                        
                        $tempFile = tempnam(sys_get_temp_dir(), 'export') . '.xlsx';
                        
                        // Let's manually trigger their build logic:
                        if (method_exists($exporter, 'export')) {
                            // Actually, let's just let it build sheets
                            $reflector = new \ReflectionClass($exporter);
                            // We can't easily bypass export(). Let's let export run, and grab the temp file it sends, or just recreate the logic.
                            // Better yet, just use their export logic but grab the content
                            $response = $exporter->export();
                            $fileToAttach = $response->getFile()->getPathname();
                            
                            \Illuminate\Support\Facades\Mail::to($data['email'])
                                ->send(new \App\Mail\FieldDataReportMail($fileToAttach, $filename));
                                
                            $notification = \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Report Shared via Email')
                                ->body('The report has been successfully emailed to ' . $data['email']);
                                
                            $notification->send();
                            $notification->sendToDatabase(auth()->user());
                        }
                        
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
                if (!$this->rawReportData || $this->rawReportData->isEmpty()) {
                    \Filament\Notifications\Notification::make()->danger()->title('No data to export.')->send();
                    return;
                }

                $exporter = null;
                $isCumulative = ($data['export_range'] ?? 'single') === 'cumulative';
                
                switch ($data['category']) {
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

                if ($exporter && method_exists($exporter, 'export')) {
                    return $exporter->export();
                }
            });
    }
}
