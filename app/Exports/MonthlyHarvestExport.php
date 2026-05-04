<?php

namespace App\Exports;

use App\Models\MonthlyHarvest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyHarvestExport
{
    protected array $records;
    protected ?int $year;
    protected ?int $month;
    protected bool $isCumulative;

    public function __construct(iterable $records, ?int $year = null, ?int $month = null, bool $isCumulative = false)
    {
        $this->records = is_array($records) ? $records : $records->all();
        $this->year = $year;
        $this->month = $month;
        $this->isCumulative = $isCumulative;
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // Remove default sheet

        // Group records by FieldSite
        $sites = [];
        foreach ($this->records as $rec) {
            $siteName = $rec->fieldSite?->name ?? 'Unknown Site';
            $siteId = $rec->field_site_id ?? 0;
            if (!isset($sites[$siteId])) {
                $sites[$siteId] = [
                    'name' => $siteName,
                    'site' => $rec->fieldSite,
                    'records' => [],
                ];
            }
            $sites[$siteId]['records'][] = $rec;
        }

        if (empty($sites)) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('No Data');
            $sheet->setCellValue('A1', 'No records found.');
        } else {
            foreach ($sites as $siteId => $siteData) {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle(substr($siteData['name'], 0, 31));
                $this->buildSheet($sheet, $siteData['records'], $siteData['site']);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Monthly_Harvest_' . now()->format('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    protected function buildSheet(Worksheet $sheet, array $records, $site)
    {
        if ($this->year && $this->month) {
            $asOfDate = Carbon::create($this->year, $this->month, 1);
        } else {
            $asOfDate = count($records) > 0 && $records[0]->report_month
                ? Carbon::parse($records[0]->report_month)
                : now();
        }

        $this->setupPage($sheet);
        $this->drawHeader($sheet, $asOfDate);
        $this->drawTableHeaders($sheet);
        $currentRow = $this->drawData($sheet, $records);
        $this->drawFooter($sheet, $currentRow + 2, $site, $records);
    }

    protected function setupPage(Worksheet $sheet)
    {
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
    }

    protected function drawHeader(Worksheet $sheet, Carbon $asOfDate)
    {
        // Logo
        $logoPath = public_path('images/PCA_DA_Logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('PCA Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(75);
            $drawing->setCoordinates('B1');
            $drawing->setWorksheet($sheet);
        }

        $mergeEnd = 'U';
        
        $sheet->mergeCells("A1:{$mergeEnd}1");
        $sheet->setCellValue('A1', 'PHILIPPINE COCONUT AUTHORITY');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$mergeEnd}2");
        $sheet->setCellValue('A2', 'COCONUT HYBRIDIZATION PROJECT-CFIDP');
        $sheet->getStyle('A2')->getFont()->setSize(10);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$mergeEnd}3");
        $sheet->setCellValue('A3', 'ON-FARM HYBRID SEEDNUT PRODUCTION');
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($this->year && !$this->month) {
            $asOfStr = 'For the year ' . $this->year;
        } elseif ($this->year && $this->month && $this->isCumulative) {
            $asOfStr = 'For the months of January to ' . $asOfDate->format('F Y');
        } else {
            $asOfStr = 'For the month of ' . $asOfDate->format('F Y');
        }
        $sheet->mergeCells("A4:{$mergeEnd}4");
        $sheet->setCellValue('A4', $asOfStr);
        $sheet->getStyle('A4')->getFont()->setSize(10)->setUnderline(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    protected function drawTableHeaders(Worksheet $sheet)
    {
        $headers = [
            'A5' => 'Farm Location',
            'B5' => 'Name of Partner',
            'C5' => 'Area (Ha.)',
            'D5' => 'Age of Palms (Years)',
            'E5' => 'No. of Hybridized Palms',
            'F5' => 'Variety / Hybrid Crosses',
            'G5' => 'Seednuts Produced',
            'H5' => 'Monthly Production (No. of Seednuts)',
            'T5' => 'TOTAL',
            'U5' => 'Remarks',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $col = 'H';
        foreach ($months as $month) {
            $sheet->setCellValue($col . '6', $month);
            $col++;
        }

        // Styling
        $styleArray = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0B9E4F'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A5:U6')->applyFromArray($styleArray);
        
        // Merging main headers
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:C6');
        $sheet->mergeCells('D5:D6');
        $sheet->mergeCells('E5:E6');
        $sheet->mergeCells('F5:F6');
        $sheet->mergeCells('G5:G6');
        $sheet->mergeCells('H5:S5');
        $sheet->mergeCells('T5:T6');
        $sheet->mergeCells('U5:U6');
    }

    protected function drawData(Worksheet $sheet, array $records)
    {
        $row = 7;
        
        // Group by Farm (location + farm_name)
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
                    'varieties' => [],
                ];
            }
            
            foreach ($rec->varieties as $v) {
                $varKey = ($v->variety ?? '') . '|' . ($v->seednuts_type ?? '');
                if (!isset($farms[$key]['varieties'][$varKey])) {
                    $farms[$key]['varieties'][$varKey] = [
                        'variety' => $v->variety,
                        'seednuts_type' => $v->seednuts_type,
                        'months' => array_fill(1, 12, 0),
                        'remarks' => $v->remarks,
                    ];
                }
                $month = $rec->report_month->month;
                $farms[$key]['varieties'][$varKey]['months'][$month] += $v->seednuts_count;
            }
        }

        $firstDataRow = $row;

        foreach ($farms as $farm) {
            $firstVarRow = $row;
            foreach ($farm['varieties'] as $v) {
                if ($row === $firstVarRow) {
                    $sheet->setCellValue('A' . $row, $farm['location']);
                    $sheet->setCellValue('B' . $row, $farm['farm_name']);
                    $sheet->setCellValue('C' . $row, $farm['area_ha']);
                    $sheet->setCellValue('D' . $row, $farm['age_of_palms']);
                    $sheet->setCellValue('E' . $row, $farm['num_hybridized_palms']);
                }
                
                $sheet->setCellValue('F' . $row, $v['variety']);
                $sheet->setCellValue('G' . $row, $v['seednuts_type']);
                
                $col = 'H';
                for($m = 1; $m <= 12; $m++) {
                    $count = $v['months'][$m];
                    if ($count > 0) {
                        $sheet->setCellValue($col . $row, $count);
                    }
                    $col++;
                }
                
                // COMPUTATIONAL: Row total = SUM(H:S) for this row
                $sheet->setCellValue('T' . $row, "=SUM(H$row:S$row)");
                $sheet->setCellValue('U' . $row, $v['remarks']);
                
                $this->applyRowBorder($sheet, $row);
                $row++;
            }
        }

        $lastDataRow = $row - 1;

        // Total Row — all formulas
        $sheet->setCellValue('B' . $row, 'TOTAL');
        // COMPUTATIONAL: SUM of area and palms
        $sheet->setCellValue('C' . $row, "=SUM(C$firstDataRow:C$lastDataRow)");
        $sheet->setCellValue('E' . $row, "=SUM(E$firstDataRow:E$lastDataRow)");
        
        // COMPUTATIONAL: Column totals for each month (H-S) and grand total (T)
        $col = 'H';
        for($m = 1; $m <= 12; $m++) {
            $sheet->setCellValue($col . $row, "=SUM({$col}{$firstDataRow}:{$col}{$lastDataRow})");
            $col++;
        }
        $sheet->setCellValue('T' . $row, "=SUM(T$firstDataRow:T$lastDataRow)");
        
        $sheet->getStyle("A$row:U$row")->getFont()->setBold(true);
        $this->applyRowBorder($sheet, $row);

        $this->autoSizeColumns($sheet);

        return $row;
    }

    protected function drawFooter(Worksheet $sheet, $startRow, $site, array $records)
    {
        $row = $startRow;
        
        // Define signatory column merge ranges
        $sigRanges = [
            'prepared' => ['start' => 'A', 'end' => 'E'],
            'reviewed' => ['start' => 'K', 'end' => 'O'],
            'noted'    => ['start' => 'S', 'end' => 'U'],
        ];
        
        // Resolve signatory labels from FieldSite overrides
        $prepLabel = $site?->prepared_by_label ?: 'Prepared by:';
        $revLabel = $site?->reviewed_by_label ?: 'Reviewed by:';
        $noteLabel = $site?->noted_by_label ?: 'Noted by:';
        $labels = ['prepared' => $prepLabel, 'reviewed' => $revLabel, 'noted' => $noteLabel];
        
        // Label row — merge and center
        foreach ($sigRanges as $key => $range) {
            $sheet->mergeCells("{$range['start']}{$row}:{$range['end']}{$row}");
            $sheet->setCellValue($range['start'] . $row, $labels[$key]);
            $sheet->getStyle("{$range['start']}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        $signatureRow = $row + 1;
        $sheet->getRowDimension($signatureRow)->setRowHeight(38);
        
        // Merge signature row
        foreach ($sigRanges as $range) {
            $sheet->mergeCells("{$range['start']}{$signatureRow}:{$range['end']}{$signatureRow}");
        }
        
        $row += 2;
        
        // Resolve signatories: FieldSite overrides → Record approval users → Blank
        $signatories = $this->resolveSignatories($site, $records);
        
        // Name row — merge, bold, center
        foreach ($sigRanges as $key => $range) {
            $sheet->mergeCells("{$range['start']}{$row}:{$range['end']}{$row}");
            $sheet->setCellValue($range['start'] . $row, $signatories[$key]['name']);
            $sheet->getStyle("{$range['start']}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("{$range['start']}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        $row++;
        // Title row — merge and center
        foreach ($sigRanges as $key => $range) {
            $sheet->mergeCells("{$range['start']}{$row}:{$range['end']}{$row}");
            $sheet->setCellValue($range['start'] . $row, $signatories[$key]['title']);
            $sheet->getStyle("{$range['start']}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Draw signature images
        $this->addSignatures($sheet, $signatureRow, $signatories, $sigRanges);
    }

    /**
     * Resolve signatories following the Django _add_footer() priority:
     * 1. FieldSite overrides (prepared_by_name/title, etc.)
     * 2. Record approval workflow users (preparedByUser, reviewedByUser, notedByUser)
     * 3. Blank placeholder
     */
    protected function resolveSignatories($site, array $records): array
    {
        // Find approval users from records (check all records, take the most recent signatory)
        $preparedUser = null;
        $reviewedUser = null;
        $notedUser = null;

        foreach (array_reverse($records) as $rec) {
            if (!$preparedUser && $rec->preparedByUser) $preparedUser = $rec->preparedByUser;
            if (!$reviewedUser && $rec->reviewedByUser) $reviewedUser = $rec->reviewedByUser;
            if (!$notedUser && $rec->notedByUser) $notedUser = $rec->notedByUser;
        }

        return [
            'prepared' => $this->resolveOneSignatory($site, 'prepared', $preparedUser, 'COS/Agriculturist'),
            'reviewed' => $this->resolveOneSignatory($site, 'reviewed', $reviewedUser, 'Senior Agriculturist'),
            'noted' => $this->resolveOneSignatory($site, 'noted', $notedUser, 'PCDM/Division Chief I'),
        ];
    }

    protected function resolveOneSignatory($site, string $role, $user, string $defaultTitle): array
    {
        $nameField = "{$role}_by_name";
        $titleField = "{$role}_by_title";

        // 1. FieldSite override
        if ($site && !empty($site->$nameField)) {
            return [
                'name' => strtoupper($site->$nameField),
                'title' => $site->$titleField ?? $defaultTitle,
                'user' => null, // No user object for signature image
            ];
        }

        // 2. Record approval user
        if ($user) {
            return [
                'name' => strtoupper($user->name),
                'title' => $user->role_title ?? $defaultTitle,
                'user' => $user,
            ];
        }

        // 3. Blank placeholder
        return [
            'name' => '_______________________',
            'title' => $defaultTitle,
            'user' => null,
        ];
    }

    protected function applyRowBorder(Worksheet $sheet, $row)
    {
        $sheet->getStyle("A$row:U$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    protected function autoSizeColumns(Worksheet $sheet)
    {
        foreach (range('A', 'U') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    protected function addSignatures(Worksheet $sheet, int $row, array $signatories, array $sigRanges)
    {
        foreach ($sigRanges as $key => $range) {
            $user = $signatories[$key]['user'] ?? null;
            if ($user && $user->signature_image) {
                try {
                    $url = \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($user->signature_image);
                    $imgData = @file_get_contents($url);
                    if ($imgData) {
                        $tmp = tempnam(sys_get_temp_dir(), 'sig');
                        file_put_contents($tmp, $imgData);
                        
                        $drawing = new Drawing();
                        $drawing->setPath($tmp);
                        $drawing->setHeight(45);
                        
                        // Place at the middle column of the merge range for natural centering
                        $startIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($range['start']);
                        $endIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($range['end']);
                        $midIdx = (int)ceil(($startIdx + $endIdx) / 2);
                        $midCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($midIdx);
                        
                        $drawing->setCoordinates($midCol . $row);
                        $drawing->setWorksheet($sheet);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Excel Signature Error: " . $e->getMessage());
                }
            }
        }
    }
}

