<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use App\Models\FieldSite;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\FieldDataReportMail;
use App\Exports\FullPackageExport;
use App\Exports\MonthlyHarvestExport;
use App\Exports\PollenProductionExport;
use App\Exports\HybridDistributionExport;
use App\Exports\NurseryOperationExport;

class GenerateAndEmailReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // Allow 10 minutes for heavy reports

    public array $formData;
    public string $email;
    public string $orientation;
    public string $paperSize;
    public array $selectedCategories;
    public bool $fullPackageMode;
    public ?int $authUserId;
    public ?int $authUserFieldSiteId;
    public ?string $authUserRole;

    /**
     * Create a new job instance.
     */
    public function __construct(
        array $formData, 
        string $email, 
        string $orientation, 
        string $paperSize,
        array $selectedCategories,
        bool $fullPackageMode,
        ?int $authUserId,
        ?int $authUserFieldSiteId,
        ?string $authUserRole
    ) {
        $this->formData = $formData;
        $this->email = $email;
        $this->orientation = $orientation;
        $this->paperSize = $paperSize;
        $this->selectedCategories = $selectedCategories;
        $this->fullPackageMode = $fullPackageMode;
        $this->authUserId = $authUserId;
        $this->authUserFieldSiteId = $authUserFieldSiteId;
        $this->authUserRole = $authUserRole;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);
        
        try {
            $isCumulative = ($this->formData['export_range'] ?? 'single') === 'cumulative';
            $fullPackageData = [];
            $activeRecords = collect();
            $exporter = null;
            $excelFilename = 'Report.xlsx';
            
            // 1. Re-fetch Data
            if ($this->fullPackageMode) {
                foreach ($this->selectedCategories as $cat) {
                    $query = $this->buildCategoryQuery($cat);
                    if (!$query) continue;
                    
                    $this->applyFilters($query, $cat);
                    $records = $query->get();
                    $grouped = $records->groupBy('field_site_id');
                    
                    $catData = [];
                    foreach ($grouped as $siteId => $siteRecords) {
                        $catData[$siteId] = [
                            'records' => $siteRecords,
                            'farms'   => $cat === 'monthly_harvest' ? $this->groupHarvestData($siteRecords) : null,
                        ];
                    }
                    $fullPackageData[$cat] = $catData;
                }
                
                $exporter = new FullPackageExport($fullPackageData, $this->formData['year'], $this->formData['month'], $isCumulative);
                $period = $this->formData['month'] ? \Carbon\Carbon::create($this->formData['year'], $this->formData['month'], 1)->format('F_Y') : $this->formData['year'];
                $excelFilename = 'Full_Report_Package_' . $period . '.xlsx';
            } else {
                $activeCat = $this->selectedCategories[0];
                $query = $this->buildCategoryQuery($activeCat);
                if ($query) {
                    $this->applyFilters($query, $activeCat);
                    $activeRecords = $query->get();
                    
                    if ($activeRecords->isNotEmpty()) {
                        switch ($activeCat) {
                            case 'monthly_harvest':
                                $exporter = new MonthlyHarvestExport($activeRecords, $this->formData['year'], $this->formData['month'], $isCumulative);
                                $excelFilename = 'Monthly_Harvest.xlsx';
                                break;
                            case 'pollen_production':
                                $exporter = new PollenProductionExport($activeRecords, $this->formData['year'], $this->formData['month'], $isCumulative);
                                $excelFilename = 'Pollen_Production.xlsx';
                                break;
                            case 'hybrid_distribution':
                                $exporter = new HybridDistributionExport($activeRecords, $this->formData['year'], $this->formData['month'], $isCumulative);
                                $excelFilename = 'Hybrid_Distribution.xlsx';
                                break;
                            case 'nursery_operation':
                            case 'terminal_report':
                                $exporter = new NurseryOperationExport($activeRecords, $this->formData['year'], $this->formData['month'], $isCumulative);
                                $excelFilename = $activeCat === 'terminal_report' ? 'Terminal_Report.xlsx' : 'Nursery_Operation.xlsx';
                                break;
                        }
                    }
                }
            }

            if (!$exporter) {
                Log::warning('GenerateAndEmailReportJob: No data found for the selected filters.');
                return;
            }

            // 2. Generate Excel
            $response = $exporter->export();
            $excelFile = $response->getFile()->getPathname();

            // 3. Generate PDF
            $pdfInfo = $this->generatePdfReport($fullPackageData, $activeRecords, $isCumulative);
            $pdfFile = $pdfInfo['path'];
            $pdfName = $pdfInfo['filename'];

            // 4. Send Email
            $filesToAttach = [$excelFile, $pdfFile];
            $fileNames = [$excelFilename, $pdfName];

            Mail::to($this->email)->send(new FieldDataReportMail($filesToAttach, $fileNames));

            // 5. Cleanup
            @unlink($excelFile);
            @unlink($pdfFile);

        } catch (\Exception $e) {
            Log::error('GenerateAndEmailReportJob Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

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

    protected function applyFilters(Builder $query, string $category): void
    {
        $query->whereYear('report_month', $this->formData['year']);

        if (!empty($this->formData['month']) && in_array($category, ['monthly_harvest', 'pollen_production', 'hybrid_distribution'])) {
            if (($this->formData['export_range'] ?? 'single') === 'cumulative') {
                $query->whereMonth('report_month', '<=', $this->formData['month']);
            } else {
                $query->whereMonth('report_month', $this->formData['month']);
            }
        }

        if (in_array($this->authUserRole, ['supervisor', 'staff'])) {
            $query->where('field_site_id', $this->authUserFieldSiteId);
        } elseif (!empty($this->formData['field_site_id'])) {
            $query->where('field_site_id', $this->formData['field_site_id']);
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
                    'num_palms' => $rec->num_palms ?? 0,
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

    protected function generatePdfReport(array $fullPackageData, $activeRecords, bool $isCumulative): array
    {
        $asOfDate = \Carbon\Carbon::create($this->formData['year'], $this->formData['month'] ?: 1, 1);
        $activeCat = $this->selectedCategories[0] ?? 'monthly_harvest';

        if ($this->formData['year'] && empty($this->formData['month'])) {
            $periodStr = in_array($activeCat, ['hybrid_distribution', 'nursery_operation', 'terminal_report'])
                ? 'as of end of ' . $this->formData['year']
                : 'For the year ' . $this->formData['year'];
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
                $catData = $fullPackageData[$cat] ?? [];
                foreach ($catData as $siteId => $siteData) {
                    $pages[] = [
                        'category' => $cat,
                        'records'  => $siteData['records'],
                        'farms'    => $siteData['farms'] ?? null,
                    ];
                }
            }
        } else {
            $grouped = $activeRecords->groupBy('field_site_id');
            foreach ($grouped as $siteId => $siteRecords) {
                $pages[] = [
                    'category' => $activeCat,
                    'records'  => $siteRecords,
                    'farms'    => $activeCat === 'monthly_harvest' ? $this->groupHarvestData($siteRecords) : null,
                ];
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
        $period = $this->formData['month'] ? $asOfDate->format('F_Y') : $this->formData['year'];
        $pdfTitle = "{$reportTitle} - {$firstSite} - {$period}";

        $pdf = Pdf::loadView('pdf.report-dashboard', [
            'pages'       => $pages,
            'periodStr'   => $periodStr,
            'title'       => $pdfTitle,
            'filterMonth' => $this->formData['month'] ?? null,
            'filterYear'  => $this->formData['year'] ?? null,
        ])->setPaper($this->paperSize, $this->orientation);

        $pdfContent = $pdf->output();

        $safeSiteName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $firstSite);
        $safeCatName = $this->fullPackageMode ? 'Full_Package' : preg_replace('/[^A-Za-z0-9_\-]/', '_', $categoryLabels[$activeCat] ?? $activeCat);
        $timestamp = now()->format('Ymd_His');
        $filename = "{$safeCatName}_{$safeSiteName}_{$period}_{$timestamp}.pdf";

        $storagePath = "reports/{$filename}";
        Storage::disk('local')->put($storagePath, $pdfContent);
        
        return [
            'path' => Storage::disk('local')->path($storagePath),
            'filename' => $filename,
        ];
    }
}
