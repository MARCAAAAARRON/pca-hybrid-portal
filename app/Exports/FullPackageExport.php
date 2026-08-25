<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * FullPackageExport
 *
 * Combines all 5 report categories into a single multi-sheet Excel workbook.
 * Each category gets its own sheet(s) — one sheet per field site per category.
 *
 * Structure:
 *   [Monthly Harvest - Loay Farm]
 *   [Monthly Harvest - Balilihan Farm]
 *   [Pollen Production - Loay Farm]
 *   ... etc
 */
class FullPackageExport
{
    protected array $fullPackageData; // [category => [site_id => ['records' => ..., 'farms' => ...]]]
    protected ?int $year;
    protected ?int $month;
    protected bool $isCumulative;

    protected const CATEGORY_LABELS = [
        'monthly_harvest'     => 'Monthly Harvest',
        'pollen_production'   => 'Pollen Production',
        'hybrid_distribution' => 'Hybrid Distribution',
        'nursery_operation'   => 'Nursery Operations',
        'terminal_report'     => 'Terminal Reports',
    ];

    protected const CATEGORY_EXPORTERS = [
        'monthly_harvest'     => MonthlyHarvestExport::class,
        'pollen_production'   => PollenProductionExport::class,
        'hybrid_distribution' => HybridDistributionExport::class,
        'nursery_operation'   => NurseryOperationExport::class,
        'terminal_report'     => NurseryOperationExport::class,
    ];

    public function __construct(array $fullPackageData, ?int $year = null, ?int $month = null, bool $isCumulative = false)
    {
        $this->fullPackageData = $fullPackageData;
        $this->year = $year;
        $this->month = $month;
        $this->isCumulative = $isCumulative;
    }

    /**
     * Build and save a combined multi-sheet workbook to a temp file.
     * Returns the path to the temp file.
     */
    public function buildToFile(): string
    {
        $combinedSpreadsheet = new Spreadsheet();
        $combinedSpreadsheet->removeSheetByIndex(0);

        foreach (self::CATEGORY_EXPORTERS as $category => $exporterClass) {
            $categoryData = $this->fullPackageData[$category] ?? [];

            if (empty($categoryData)) {
                // Add a placeholder "No Data" sheet for this category
                $sheet = $combinedSpreadsheet->createSheet();
                $catLabel = self::CATEGORY_LABELS[$category] ?? $category;
                $sheet->setTitle(substr($catLabel . ' - No Data', 0, 31));
                $sheet->setCellValue('A1', 'No records found for ' . $catLabel . '.');
                continue;
            }

            // Collect all records across all sites for this category
            $allRecords = collect();
            foreach ($categoryData as $siteId => $siteData) {
                $allRecords = $allRecords->merge($siteData['records']);
            }

            // Build the exporter and get its spreadsheet
            $exporter = new $exporterClass($allRecords, $this->year, $this->month, $this->isCumulative);

            // Build a temporary spreadsheet via the exporter's internal logic
            $tempSpreadsheet = $this->buildExporterSpreadsheet($exporter, $allRecords, $category);

            // Copy all sheets from the category spreadsheet into the combined one
            $catLabel = self::CATEGORY_LABELS[$category] ?? $category;
            foreach ($tempSpreadsheet->getAllSheets() as $sheet) {
                // Prefix sheet title with category abbreviation if multi-category
                $originalTitle = $sheet->getTitle();
                $prefix = substr($this->getCategoryPrefix($category), 0, 5);
                $newTitle = substr($prefix . ' - ' . $originalTitle, 0, 31);
                
                $sheet->setTitle($newTitle);
                $combinedSpreadsheet->addExternalSheet($sheet);
            }
        }

        // Save to temp file
        $writer = new Xlsx($combinedSpreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'full_pkg') . '.xlsx';
        $writer->save($tempFile);

        // Clean up component temp files now that the combined file is saved
        foreach ($this->tempFilesToCleanup as $cleanupFile) {
            @unlink($cleanupFile);
        }
        $this->tempFilesToCleanup = [];

        return $tempFile;
    }

    /**
     * Download response for the full package.
     */
    public function export()
    {
        $tempFile = $this->buildToFile();

        $period = $this->month
            ? \Carbon\Carbon::create($this->year, $this->month, 1)->format('F_Y')
            : ($this->year ?? now()->year);

        $fileName = 'Full_Report_Package_' . $period . '.xlsx';

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * @var array
     */
    protected array $tempFilesToCleanup = [];

    /**
     * Build a Spreadsheet object from a category exporter
     * by calling export() and reading the temp file it saves.
     */
    protected function buildExporterSpreadsheet($exporter, $records, string $category): Spreadsheet
    {
        // Trigger the exporter to write to a temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'cat_export') . '.xlsx';

        // All our exporters have the same internal structure.
        // We invoke buildToTempFile() if available, otherwise call export() and capture the file.
        if (method_exists($exporter, 'buildToTempFile')) {
            $exporter->buildToTempFile($tempFile);
        } else {
            // Fallback: call export() and grab the file from the response
            $response = $exporter->export();
            $responseFile = $response->getFile()->getPathname();
            copy($responseFile, $tempFile);
            @unlink($responseFile);
        }

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($tempFile);
        
        // Defer deleting the temp file because PhpSpreadsheet references images inside it via zip stream
        $this->tempFilesToCleanup[] = $tempFile;

        return $spreadsheet;
    }

    protected function getCategoryPrefix(string $category): string
    {
        return match ($category) {
            'monthly_harvest'     => 'MH',
            'pollen_production'   => 'PP',
            'hybrid_distribution' => 'HD',
            'nursery_operation'   => 'NO',
            'terminal_report'     => 'TR',
            default               => strtoupper(substr($category, 0, 2)),
        };
    }
}
