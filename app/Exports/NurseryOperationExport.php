<?php

namespace App\Exports;

use App\Models\NurseryOperation;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NurseryOperationExport
{
    protected array $records;
    protected ?int $year;
    protected ?int $month;
    protected bool $isCumulative;

    public function __construct(iterable $records, ?int $year = null, ?int $month = null, bool $isCumulative = false)
    {
        // Ensure we have batches and varieties loaded
        if ($records instanceof \Illuminate\Database\Eloquent\Builder) {
            $this->records = $records->with(['batches.varieties', 'fieldSite'])->get()->all();
        } else {
            $this->records = is_array($records) ? $records : $records->all();
        }
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
        $fileName = 'Nursery_Operations_' . now()->format('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_nursery');
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
        $this->applyColumnWidths($sheet);
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
        $logoPath = public_path('images/PCA_DA_Logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('PCA Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(75);
            $drawing->setCoordinates('B1');
            $drawing->setWorksheet($sheet);
        }

        $mergeEnd = 'R';
        
        $sheet->mergeCells("A1:{$mergeEnd}1");
        $sheet->setCellValue('A1', 'COCONUT HYBRIDIZATION PROGRAM-CFIDP');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$mergeEnd}2");
        $sheet->setCellValue('A2', 'COMMUNAL NURSERY ESTABLISHMENT');
        $sheet->getStyle('A2')->getFont()->setSize(10);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$mergeEnd}3");
        $sheet->setCellValue('A3', 'Communal Nursery Establishment Monthly Report');
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
    }

    protected function drawTableHeaders(Worksheet $sheet)
    {
        $headers = [
            'Region / Province / District',
            'Barangay / Municipality',
            'Entity Name',
            'Representative',
            'Target No. of Seednuts',
            'No. of Seednuts Harvested',
            'Date Harvested',
            'Date Seednuts Received',
            'Source of Seednuts',
            'Type/Variety',
            'No. of Seednuts Sown',
            'Date Seednut Sown',
            'No. of Seedlings Germinated',
            'No. of Ungerminated Seednuts',
            'No. of Culled Seedlings',
            'No. of Good Seedlings @ 1 ft',
            'No. of Ready to Plant (Polybagged)',
            'No. of Seedlings Dispatched',
        ];

        $col = 1;
        foreach ($headers as $h) {
            $sheet->setCellValue([$col, 5], $h);
            $col++;
        }

        $styleArray = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0B9E4F']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $sheet->getStyle('A5:R5')->applyFromArray($styleArray);
        $sheet->getRowDimension(5)->setRowHeight(42);
    }

    protected function drawData(Worksheet $sheet, array $records)
    {
        $row = 6;

        foreach ($records as $rec) {
            $batches = $rec->batches;

            if ($batches->isEmpty()) {
                $vals = [
                    $rec->region_province_district,
                    $rec->barangay_municipality,
                    $rec->proponent_entity,
                    $rec->proponent_representative,
                    $rec->target_seednuts,
                    '', '', '', '', '', '', '', '', '', '', '', '', ''
                ];
                $col = 1;
                foreach ($vals as $v) {
                    $sheet->setCellValue([$col, $row], $v);
                    $col++;
                }
                $this->applyDataRowStyle($sheet, $row);
                $row++;
                continue;
            }

            $startRow = $row;

            foreach ($batches as $batch) {
                $varieties = $batch->varieties;
                
                if ($varieties->isEmpty()) {
                    $vals = [
                        $rec->region_province_district,
                        $rec->barangay_municipality,
                        $rec->proponent_entity,
                        $rec->proponent_representative,
                        $rec->target_seednuts,
                        $batch->seednuts_harvested,
                        $batch->date_harvested,
                        $batch->date_received,
                        $batch->source_of_seednuts,
                        '', '', '', '', '', '', '', '', ''
                    ];
                    $col = 1;
                    foreach ($vals as $v) {
                        $sheet->setCellValue([$col, $row], $v);
                        $col++;
                    }
                    $this->applyDataRowStyle($sheet, $row);
                    $row++;
                    continue;
                }

                $batchStartRow = $row;
                foreach ($varieties as $v) {
                    $vals = [
                        $rec->region_province_district,
                        $rec->barangay_municipality,
                        $rec->proponent_entity,
                        $rec->proponent_representative,
                        $rec->target_seednuts,
                        $batch->seednuts_harvested,
                        $batch->date_harvested,
                        $batch->date_received,
                        $batch->source_of_seednuts,
                        $v->variety,
                        $v->seednuts_sown,
                        $v->date_sown,
                        $v->seedlings_germinated,
                        $v->ungerminated_seednuts,
                        $v->culled_seedlings,
                        $v->good_seedlings,
                        $v->ready_to_plant,
                        $v->seedlings_dispatched,
                    ];
                    
                    $col = 1;
                    foreach ($vals as $valItem) {
                        $sheet->setCellValue([$col, $row], $valItem);
                        $col++;
                    }
                    $this->applyDataRowStyle($sheet, $row);
                    $row++;
                }

                $batchEndRow = $row - 1;
                if ($batchEndRow > $batchStartRow) {
                    for ($c = 6; $c <= 9; $c++) { // Batch columns F to I
                        $sheet->mergeCells([$c, $batchStartRow, $c, $batchEndRow]);
                        $range = Coordinate::stringFromColumnIndex($c) . $batchStartRow . ':' . Coordinate::stringFromColumnIndex($c) . $batchEndRow;
                        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle($range)->getAlignment()->setWrapText(true);
                    }
                }
            }

            // Merge operation (parent) columns
            $endRow = $row - 1;
            if ($endRow > $startRow) {
                for ($c = 1; $c <= 5; $c++) { // Operation columns A to E
                    $sheet->mergeCells([$c, $startRow, $c, $endRow]);
                    $range = Coordinate::stringFromColumnIndex($c) . $startRow . ':' . Coordinate::stringFromColumnIndex($c) . $endRow;
                    $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($range)->getAlignment()->setWrapText(true);
                }
            }
        }

        return $row;
    }

    protected function applyColumnWidths(Worksheet $sheet): void
    {
        $widths = [
            'A' => 24, 'B' => 22, 'C' => 22, 'D' => 20, 'E' => 16, 'F' => 16,
            'G' => 16, 'H' => 16, 'I' => 22, 'J' => 18, 'K' => 16, 'L' => 16,
            'M' => 16, 'N' => 16, 'O' => 16, 'P' => 18, 'Q' => 22, 'R' => 18,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    protected function applyDataRowStyle(Worksheet $sheet, int $row): void
    {
        $sheet->getStyle("A{$row}:R{$row}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Let wrapped rows grow naturally to prevent clipped/overlapping text.
        $sheet->getRowDimension($row)->setRowHeight(-1);
    }

    protected function drawFooter(Worksheet $sheet, $startRow, $site, array $records)
    {
        $row = $startRow;
        
        $sigRanges = [
            'prepared' => ['start' => 'B', 'end' => 'E'],
            'reviewed' => ['start' => 'H', 'end' => 'K'],
            'noted'    => ['start' => 'N', 'end' => 'R'],
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
                        
                        $startIdx = Coordinate::columnIndexFromString($range['start']);
                        $endIdx = Coordinate::columnIndexFromString($range['end']);
                        $midIdx = (int)ceil(($startIdx + $endIdx) / 2);
                        $midCol = Coordinate::stringFromColumnIndex($midIdx);
                        
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

