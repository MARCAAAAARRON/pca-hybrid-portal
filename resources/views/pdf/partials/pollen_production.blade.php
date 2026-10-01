@php
    $site = $reportData->first()->fieldSite ?? null;
    $centerText = $site?->name ?? 'Unknown';
    if (str_contains(strtolower($centerText), 'loay')) {
        $centerText = 'LOAY CODE FARM, LAS SALINAS SUR, LOAY, BOHOL';
    }
    $pollenVar = count($reportData) > 0 ? $reportData->first()->pollen_variety : '';
@endphp
<p style="font-weight: bold; font-size: 9px; margin-bottom: 2px;">CENTER/UNIT: {{ $centerText }}</p>
<p style="font-weight: bold; font-size: 9px; margin-bottom: 10px;">POLLEN VARIETY: {{ $pollenVar }}</p>

<table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 8px; line-height: 1.2;">
    <thead>
        <tr style="background-color: #0B9E4F; color: white;">
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;" rowspan="2">MONTH</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;" rowspan="2">Ending Balance<br>Last Month<br>(g Pollens)</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;" colspan="3">POLLENS RECEIVED FROM OTHER CENTER</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;" colspan="6">POLLEN UTILIZATION (grams of Pollen) per Week</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;" rowspan="2">Ending Balance<br>(g Pollens)</th>
        </tr>
        <tr style="background-color: #0B9E4F; color: white;">
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;">Source</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;">Date Received<br>mm/dd/yyyy</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;">Grams of<br>Pollens</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;">Week 1</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;">Week 2</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;">Week 3</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;">Week 4</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;">Week 5</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;">TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalReceived = 0;
            $totalUtil = 0;
        @endphp
        
        @foreach($reportData as $rec)
            @php
                $utilTotal = floatval($rec->week1) + floatval($rec->week2) + floatval($rec->week3) + floatval($rec->week4) + floatval($rec->week5);
                $endBalance = floatval($rec->ending_balance_prev) + floatval($rec->pollens_received) - $utilTotal;
                
                $totalReceived += floatval($rec->pollens_received);
                $totalUtil += $utilTotal;
            @endphp
            <tr>
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ $rec->month_label }}</td>
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: right; white-space: nowrap;">{{ number_format($rec->ending_balance_prev, 2) }}</td>
                <td style="border: 1px solid #000; padding: 2px 4px;">{{ $rec->pollen_source }}</td>
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; white-space: nowrap;">{{ $rec->date_received ? \Carbon\Carbon::parse($rec->date_received)->format('m/d/Y') : '' }}</td>
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: right; white-space: nowrap;">{{ number_format($rec->pollens_received, 2) }}</td>
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: right; white-space: nowrap;">{{ number_format($rec->week1, 2) }}</td>
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: right; white-space: nowrap;">{{ number_format($rec->week2, 2) }}</td>
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: right; white-space: nowrap;">{{ number_format($rec->week3, 2) }}</td>
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: right; white-space: nowrap;">{{ number_format($rec->week4, 2) }}</td>
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: right; white-space: nowrap;">{{ number_format($rec->week5, 2) }}</td>
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: right; font-weight: 600; white-space: nowrap;">{{ number_format($utilTotal, 2) }}</td>
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: right; font-weight: bold; white-space: nowrap;">{{ number_format($endBalance, 2) }}</td>
            </tr>
        @endforeach
        
        <tr style="font-weight: bold; background-color: #f3f4f6;">
            <td style="border: 1px solid #000; padding: 2px 4px; text-align: right;" colspan="4">TOTAL:</td>
            <td style="border: 1px solid #000; padding: 2px 4px; text-align: right; white-space: nowrap;">{{ number_format($totalReceived, 2) }}</td>
            <td style="border: 1px solid #000; padding: 2px 4px;" colspan="5"></td>
            <td style="border: 1px solid #000; padding: 2px 4px; text-align: right; white-space: nowrap;">{{ number_format($totalUtil, 2) }}</td>
            <td style="border: 1px solid #000; padding: 2px 4px;"></td>
        </tr>
    </tbody>
</table>
