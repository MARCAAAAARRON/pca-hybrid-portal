<?php

namespace App\Exports;

use App\Models\HybridDistribution;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HybridDistributionExport
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
        $fileName = 'Hybrid_Distribution_' . now()->format('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_dist');
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

        $siteName = $site?->name ?? 'Unknown Site';

        $this->setupPage($sheet);
        $this->drawHeader($sheet, $asOfDate);
        $this->drawTableHeaders($sheet);
        $currentRow = $this->drawData($sheet, $records, $siteName);
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
        $logoPath = public_path('images/PCA_DA_Logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('PCA Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(75);
            $drawing->setCoordinates('B1');
            $drawing->setWorksheet($sheet);
        }

        $mergeEnd = 'S';
        
        $sheet->mergeCells("A1:{$mergeEnd}1");
        $sheet->setCellValue('A1', 'Department of Agriculture');
        $sheet->getStyle('A1')->getFont()->setSize(10);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$mergeEnd}2");
        $sheet->setCellValue('A2', 'PHILIPPINE COCONUT AUTHORITY');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$mergeEnd}3");
        $sheet->setCellValue('A3', 'REGION VII');
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($this->year && !$this->month) {
            $asOfStr = 'as of end of ' . $this->year;
        } elseif ($this->year && $this->month && $this->isCumulative) {
            $asOfStr = 'Cumulative as of ' . $asOfDate->endOfMonth()->format('F d, Y');
        } else {
            $asOfStr = 'as of ' . $asOfDate->endOfMonth()->format('F d, Y');
        }
        $sheet->mergeCells("A4:{$mergeEnd}4");
        $sheet->setCellValue('A4', $asOfStr);
        $sheet->getStyle('A4')->getFont()->setSize(10)->setUnderline(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A5:{$mergeEnd}5");
        $sheet->setCellValue('A5', 'COCONUT HYBRIDIZATION PROGRAM');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A6:{$mergeEnd}6");
        $sheet->setCellValue('A6', 'COMMUNAL NURSERY: DISPATCHED SEEDLINGS');
        $sheet->getStyle('A6')->getFont()->setSize(10);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    protected function drawTableHeaders(Worksheet $sheet)
    {
        $mainHeaders = [
            'A8' => 'Region', 'B8' => 'Province', 'C8' => 'District', 'D8' => 'Municipality',
            'E8' => 'Barangay', 'F8' => 'Name of Farmer Participant', 'I8' => 'Gender',
            'K8' => 'Farm Location', 'N8' => 'Seedlings Received',
            'O8' => 'Date Received', 'P8' => 'Type/Variety',
            'Q8' => 'No. of Seedlings Planted', 'R8' => 'Date Planted', 'S8' => 'REMARKS',
        ];
        $subHeaders9 = ['F9' => 'Family Name', 'G9' => 'Given Name', 'H9' => 'M.I.'];
        $subHeaders10 = ['I10' => 'Male', 'J10' => 'Female', 'K10' => 'Barangay', 'L10' => 'Municipality', 'M10' => 'Province'];

        foreach ($mainHeaders as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        foreach ($subHeaders9 as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        foreach ($subHeaders10 as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $styleArray = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0B9E4F']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $sheet->getStyle('A8:S10')->applyFromArray($styleArray);
    }

    protected function drawData(Worksheet $sheet, array $records, string $siteName)
    {
        // Province & Site labels
        $sheet->mergeCells('A11:S11');
        $sheet->setCellValue('A11', 'BOHOL PROVINCE');
        $sheet->getStyle('A11')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A12:S12');
        $sheet->setCellValue('A12', "COMMUNAL NURSERY AT {$siteName}");
        $sheet->getStyle('A12')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 13;
        $totalPlanted = 0;
        $totalReceived = 0;

        foreach ($records as $rec) {
            $sheet->setCellValue('A' . $row, $rec->region);
            $sheet->setCellValue('B' . $row, $rec->province);
            $sheet->setCellValue('C' . $row, $rec->district);
            $sheet->setCellValue('D' . $row, $rec->municipality);
            $sheet->setCellValue('E' . $row, $rec->barangay);
            $sheet->setCellValue('F' . $row, $rec->farmer_last_name);
            $sheet->setCellValue('G' . $row, $rec->farmer_first_name);
            $sheet->setCellValue('H' . $row, $rec->farmer_middle_initial);
            
            $gender = $rec->gender ?? '';
            $sheet->setCellValue('I' . $row, $gender === 'M' ? '/' : '');
            $sheet->setCellValue('J' . $row, $gender === 'F' ? '/' : '');
            
            $sheet->setCellValue('K' . $row, $rec->farm_barangay);
            $sheet->setCellValue('L' . $row, $rec->farm_municipality);
            $sheet->setCellValue('M' . $row, $rec->farm_province ?? 'Bohol');
            $sheet->setCellValue('N' . $row, $rec->seedlings_received);
            $sheet->setCellValue('O' . $row, $rec->date_received?->format('m/d/Y') ?? '');
            $sheet->setCellValue('P' . $row, $rec->variety);
            $sheet->setCellValue('Q' . $row, $rec->seedlings_planted);
            $sheet->setCellValue('R' . $row, $rec->date_planted?->format('m/d/Y') ?? '');
            $sheet->setCellValue('S' . $row, $rec->remarks);
            
            $sheet->getStyle("A$row:S$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            $totalPlanted += (int)$rec->seedlings_planted;
            try { $totalReceived += (int)str_replace(',', '', $rec->seedlings_received); } catch (\Exception $e) {}
            
            $row++;
        }

        // Total Row
        $sheet->setCellValue('F' . $row, 'TOTAL:');
        $sheet->getStyle('F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        if ($totalReceived > 0) {
            $sheet->setCellValue('N' . $row, $totalReceived);
            $sheet->getStyle('N' . $row)->getFont()->setBold(true);
        }
        $sheet->setCellValue('Q' . $row, $totalPlanted);
        $sheet->getStyle('Q' . $row)->getFont()->setBold(true);
        
        for ($c = 1; $c <= 19; $c++) {
            $sheet->getCellByColumnAndRow($c, $row)->getStyle()->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getCellByColumnAndRow($c, $row)->getStyle()->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        }

        // Auto-size columns
        foreach (range('A', 'S') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        return $row;
    }

    protected function drawFooter(Worksheet $sheet, $startRow, $site, array $records)
    {
        $row = $startRow;
        
        $sigRanges = [
            'prepared' => ['start' => 'A', 'end' => 'D'],
            'reviewed' => ['start' => 'H', 'end' => 'K'],
            'noted'    => ['start' => 'O', 'end' => 'S'],
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

