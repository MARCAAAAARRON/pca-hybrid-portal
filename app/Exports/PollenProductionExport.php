<?php

namespace App\Exports;

use App\Models\PollenProduction;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PollenProductionExport
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
        $spreadsheet->removeSheetByIndex(0);

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
        $fileName = 'Pollen_Production_' . now()->format('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_pollen');
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
        $this->drawHeader($sheet, $asOfDate, $site, $records);
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

    protected function drawHeader(Worksheet $sheet, Carbon $asOfDate, $site, array $records)
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

        $mergeEnd = 'L';
        
        $sheet->mergeCells("A1:{$mergeEnd}1");
        $sheet->setCellValue('A1', 'COCONUT HYBRIDIZATION PROGRAM-CFIDP');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$mergeEnd}2");
        $sheet->setCellValue('A2', 'POLLEN PRODUCTION');
        $sheet->getStyle('A2')->getFont()->setSize(10);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $siteName = $site?->name ?? 'All Sites';
        $sheet->mergeCells("A3:{$mergeEnd}3");
        $sheet->setCellValue('A3', "Pollen Production and Inventory Monthly Report — {$siteName}");
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

        // Center/Unit info
        $centerText = $site?->name ?? 'Unknown';
        if (str_contains(strtolower($centerText), 'loay')) {
            $centerText = 'LOAY CODE FARM, LAS SALINAS SUR, LOAY, BOHOL';
        }
        
        $sheet->mergeCells("A6:{$mergeEnd}6");
        $sheet->setCellValue('A6', "CENTER/UNIT: {$centerText}");
        $sheet->getStyle('A6')->getFont()->setBold(true);

        $pollenVar = count($records) > 0 ? $records[0]->pollen_variety : '';
        $sheet->mergeCells("A7:{$mergeEnd}7");
        $sheet->setCellValue('A7', "POLLEN VARIETY: {$pollenVar}");
        $sheet->getStyle('A7')->getFont()->setBold(true);
    }

    protected function drawTableHeaders(Worksheet $sheet)
    {
        $headers9 = [
            'A9' => 'MONTH',
            'B9' => "Ending Balance\nLast Month\n(g Pollens)",
            'C9' => 'POLLENS RECEIVED FROM OTHER CENTER',
            'F9' => 'POLLEN UTILIZATION (grams of Pollen) per Week',
            'L9' => "Ending Balance\n(g Pollens)",
        ];

        foreach ($headers9 as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headers10 = [
            'C10' => 'Source',
            'D10' => "Date Received\nmm/dd/yyyy",
            'E10' => "Grams of\nPollens",
            'F10' => 'Week 1',
            'G10' => 'Week 2',
            'H10' => 'Week 3',
            'I10' => 'Week 4',
            'J10' => 'Week 5',
            'K10' => 'TOTAL',
        ];

        foreach ($headers10 as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

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

        $sheet->getStyle('A9:L10')->applyFromArray($styleArray);
        
        $sheet->mergeCells('A9:A10');
        $sheet->mergeCells('B9:B10');
        $sheet->mergeCells('C9:E9');
        $sheet->mergeCells('F9:K9');
        $sheet->mergeCells('L9:L10');
    }

    protected function drawData(Worksheet $sheet, array $records)
    {
        $row = 11;
        $totalReceived = 0;
        $totalUtil = 0;

        foreach ($records as $index => $rec) {
            $currentRow = $row;
            $sheet->setCellValue('A' . $row, $rec->month_label);
            $sheet->setCellValue('B' . $row, $rec->ending_balance_prev > 0 ? $rec->ending_balance_prev : 0);
            $sheet->setCellValue('C' . $row, $rec->pollen_source);
            $sheet->setCellValue('D' . $row, $rec->date_received?->format('m/d/Y') ?? '');
            $sheet->setCellValue('E' . $row, $rec->pollens_received > 0 ? $rec->pollens_received : 0);
            $sheet->setCellValue('F' . $row, $rec->week1 > 0 ? $rec->week1 : 0);
            $sheet->setCellValue('G' . $row, $rec->week2 > 0 ? $rec->week2 : 0);
            $sheet->setCellValue('H' . $row, $rec->week3 > 0 ? $rec->week3 : 0);
            $sheet->setCellValue('I' . $row, $rec->week4 > 0 ? $rec->week4 : 0);
            $sheet->setCellValue('J' . $row, $rec->week5 > 0 ? $rec->week5 : 0);
            
            // COMPUTATIONAL: Total Utilization = Sum of Week 1-5
            $sheet->setCellValue('K' . $row, "=SUM(F$currentRow:J$currentRow)");
            
            // COMPUTATIONAL: Ending Balance = Prev + Received - Utilized
            $sheet->setCellValue('L' . $row, "=B$currentRow + E$currentRow - K$currentRow");
            
            // Formatting
            $sheet->getStyle("B$row:L$row")->getNumberFormat()->setFormatCode('#,##0.00 "g"');
            $sheet->getStyle("A$row:L$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        $lastDataRow = $row - 1;

        // Total Row
        $sheet->setCellValue('A' . $row, 'TOTAL:');
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // COMPUTATIONAL: Totals for Received and Utilization
        $sheet->setCellValue('E' . $row, "=SUM(E11:E$lastDataRow)");
        $sheet->setCellValue('K' . $row, "=SUM(K11:K$lastDataRow)");

        $sheet->getStyle("E$row:K$row")->getNumberFormat()->setFormatCode('#,##0.00 "g"');
        $sheet->getStyle("A$row:L$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:L$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $this->autoSizeColumns($sheet);
        return $row;
    }

    protected function formatWeight($grams)
    {
        if ($grams >= 1000) {
            return number_format($grams / 1000, 2) . ' kg';
        }
        return number_format($grams, 2) . ' g';
    }

    protected function drawFooter(Worksheet $sheet, $startRow, $site, array $records)
    {
        $row = $startRow;
        
        $sigRanges = [
            'prepared' => ['start' => 'A', 'end' => 'C'],
            'reviewed' => ['start' => 'F', 'end' => 'H'],
            'noted'    => ['start' => 'I', 'end' => 'L'],
        ];
        
        $prepLabel = $site?->prepared_by_label ?: 'Prepared by:';
        $revLabel = $site?->reviewed_by_label ?: 'Reviewed by:';
        $noteLabel = $site?->noted_by_label ?: 'Noted by:';
        $labels = ['prepared' => $prepLabel, 'reviewed' => $revLabel, 'noted' => $noteLabel];
        
        foreach ($sigRanges as $key => $range) {
            $sheet->mergeCells("{$range['start']}{$row}:{$range['end']}{$row}");
            $sheet->setCellValue($range['start'] . $row, $labels[$key]);
            $sheet->getStyle("{$range['start']}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        $signatureRow = $row + 1;
        $sheet->getRowDimension($signatureRow)->setRowHeight(38);
        foreach ($sigRanges as $range) {
            $sheet->mergeCells("{$range['start']}{$signatureRow}:{$range['end']}{$signatureRow}");
        }
        $row += 2;
        
        $signatories = $this->resolveSignatories($site, $records);
        
        foreach ($sigRanges as $key => $range) {
            $sheet->mergeCells("{$range['start']}{$row}:{$range['end']}{$row}");
            $sheet->setCellValue($range['start'] . $row, $signatories[$key]['name']);
            $sheet->getStyle("{$range['start']}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("{$range['start']}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        $row++;
        foreach ($sigRanges as $key => $range) {
            $sheet->mergeCells("{$range['start']}{$row}:{$range['end']}{$row}");
            $sheet->setCellValue($range['start'] . $row, $signatories[$key]['title']);
            $sheet->getStyle("{$range['start']}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $this->addSignatures($sheet, $signatureRow, $signatories, $sigRanges);
    }

    protected function resolveSignatories($site, array $records): array
    {
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

        if ($site && !empty($site->$nameField)) {
            return ['name' => strtoupper($site->$nameField), 'title' => $site->$titleField ?? $defaultTitle, 'user' => null];
        }

        if ($user) {
            return ['name' => strtoupper($user->name), 'title' => $user->role_title ?? $defaultTitle, 'user' => $user];
        }

        return ['name' => '_______________________', 'title' => $defaultTitle, 'user' => null];
    }

    protected function autoSizeColumns(Worksheet $sheet)
    {
        foreach (range('A', 'L') as $columnID) {
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

