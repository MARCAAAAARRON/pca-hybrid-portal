<?php

namespace App\Exports;

use ZipArchive;

/**
 * FullPackageExport
 *
 * Combines all 5 report categories into a single ZIP archive containing
 * separate Excel files for each category.
 *
 * Structure:
 *   Full_Report_Package.zip
 *     ├── Monthly Harvest.xlsx (with sheets: Balilihan, Loay)
 *     ├── Pollen Production.xlsx
 *     ... etc
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
     * Build all category Excel files and bundle them into a ZIP archive.
     * Returns the path to the temp ZIP file.
     */
    public function buildToFile(): string
    {
        $zipFile = tempnam(sys_get_temp_dir(), 'full_pkg') . '.zip';
        $zip = new ZipArchive();
        
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create zip archive at {$zipFile}");
        }

        $tempFiles = [];

        foreach (self::CATEGORY_EXPORTERS as $category => $exporterClass) {
            $categoryData = $this->fullPackageData[$category] ?? [];

            if (empty($categoryData)) {
                continue; // Skip categories with no data
            }

            // Collect all records across all sites for this category
            $allRecords = collect();
            foreach ($categoryData as $siteId => $siteData) {
                $allRecords = $allRecords->merge($siteData['records']);
            }

            // Build the exporter
            $exporter = new $exporterClass($allRecords, $this->year, $this->month, $this->isCumulative);
            
            // Get the Excel file for this category
            $tempExcelFile = tempnam(sys_get_temp_dir(), 'cat_excel') . '.xlsx';
            if (method_exists($exporter, 'buildToTempFile')) {
                $exporter->buildToTempFile($tempExcelFile);
            } else {
                $response = $exporter->export();
                $responseFile = $response->getFile()->getPathname();
                copy($responseFile, $tempExcelFile);
                @unlink($responseFile);
            }

            $catLabel = self::CATEGORY_LABELS[$category] ?? $category;
            $safeFileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $catLabel) . '.xlsx';
            
            $zip->addFile($tempExcelFile, $safeFileName);
            $tempFiles[] = $tempExcelFile;
        }

        if ($zip->numFiles === 0) {
            // Add a dummy file if zip is empty so it's valid
            $zip->addFromString('No_Data.txt', 'No records found for the selected period.');
        }

        $zip->close();

        // Clean up temporary component files
        foreach ($tempFiles as $file) {
            @unlink($file);
        }

        return $zipFile;
    }

    /**
     * Download response for the full package as a ZIP file.
     */
    public function export()
    {
        $tempFile = $this->buildToFile();

        $period = $this->month
            ? \Carbon\Carbon::create($this->year, $this->month, 1)->format('F_Y')
            : ($this->year ?? now()->year);

        $fileName = 'Full_Report_Package_' . $period . '.zip';

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
