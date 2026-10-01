<?php

namespace App\Services;

use App\Models\FieldSite;
use App\Models\HybridDistribution;
use App\Models\HybridizationRecord;
use App\Models\MonthlyHarvest;
use App\Models\NurseryOperation;
use App\Models\PollenProduction;
use App\Models\Report;
use Illuminate\Support\Facades\Storage;

class PdfReportService
{
    /**
     * Generate a PDF report for the specified module and filters.
     *
     * @param string $module  One of: distribution, harvest, nursery, pollen, hybridization
     * @param array  $filters ['field_site_id' => int|null, 'year' => int|null, 'month' => int|null]
     * @return Report
     */
    public function generate(string $module, array $filters = []): Report
    {
        $user = auth()->user();
        $isSupervisor = ($user?->isSupervisor() || $user?->isSubSupervisor()) ?? false;
        $canBypassSiteScope = $user && ($user->isManager() || $user->isAdmin() || $user->isSuperAdmin());
        $effectiveFieldSiteId = $isSupervisor
            ? $user->field_site_id
            : ($filters['field_site_id'] ?? null);

        $query = $this->getQuery($module);

        // Apply filters
        if (!empty($effectiveFieldSiteId)) {
            if ($canBypassSiteScope && !$isSupervisor) {
                $query->withoutGlobalScopes()->where('field_site_id', $effectiveFieldSiteId);
            } else {
                $query->where('field_site_id', $effectiveFieldSiteId);
            }
        }
        if (!empty($filters['year'])) {
            $dateColumn = $module === 'hybridization' ? 'date_planted' : 'report_month';
            $query->whereYear($dateColumn, $filters['year']);
        }
        if (!empty($filters['month']) && $module !== 'hybridization') {
            $query->whereMonth('report_month', $filters['month']);
        }

        $records = $query->get();

        // Signatory Logic
        $signatories = [
            'prepared' => auth()->user(),
            'reviewed' => null,
            'noted' => null,
        ];

        // If filtering for a specific site/month, try to find the official record to get exact signatories
        if (!empty($effectiveFieldSiteId) && !empty($filters['month']) && !empty($filters['year'])) {
            $officialRecord = $records->first(); // Typically there's one monthly record per site
            if ($officialRecord) {
                if ($officialRecord->preparedByUser) $signatories['prepared'] = $officialRecord->preparedByUser;
                if ($officialRecord->reviewedByUser) $signatories['reviewed'] = $officialRecord->reviewedByUser;
                if ($officialRecord->notedByUser) $signatories['noted'] = $officialRecord->notedByUser;
            }
        }

        $fieldSite = !empty($effectiveFieldSiteId)
            ? FieldSite::find($effectiveFieldSiteId)?->name ?? 'All Sites'
            : 'All Sites';

        $filters['field_site_id'] = $effectiveFieldSiteId;

        // Generate PDF using Blade view
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView("reports.{$module}", [
            'records' => $records,
            'fieldSite' => $fieldSite,
            'year' => $filters['year'] ?? now()->year,
            'month' => $filters['month'] ?? null,
            'generatedAt' => now()->timezone('Asia/Manila')->format('F j, Y h:i A'),
            'signatories' => $signatories,
        ]);

        $pdf->setPaper('letter', 'landscape');

        // Store the PDF
        $filename = "{$module}_report_" . now()->format('Ymd_His') . '.pdf';
        $path = "reports/{$filename}";
        Storage::disk('public')->put($path, $pdf->output());

        // Track in Report model
        return Report::create([
            'generated_by' => auth()->id(),
            'report_type' => $module,
            'format' => 'pdf',
            'file_path' => $path,
            'parameters' => $filters,
        ]);
    }

    private function getQuery(string $module)
    {
        return match ($module) {
            'distribution' => HybridDistribution::with('fieldSite'),
            'harvest' => MonthlyHarvest::with(['fieldSite', 'varieties']),
            'nursery' => NurseryOperation::with(['fieldSite', 'batches']),
            'pollen' => PollenProduction::with('fieldSite'),
            'hybridization' => HybridizationRecord::with(['fieldSite', 'creator']),
            default => throw new \InvalidArgumentException("Unknown module: {$module}"),
        };
    }
}
