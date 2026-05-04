<div class="mb-4">
    @php
        $site = $reportData->first()->fieldSite ?? null;
        $centerText = $site?->name ?? 'Unknown';
        if (str_contains(strtolower($centerText), 'loay')) {
            $centerText = 'LOAY CODE FARM, LAS SALINAS SUR, LOAY, BOHOL';
        }
        $pollenVar = count($reportData) > 0 ? $reportData->first()->pollen_variety : '';
    @endphp
    <p class="font-bold">CENTER/UNIT: {{ $centerText }}</p>
    <p class="font-bold mt-1">POLLEN VARIETY: {{ $pollenVar }}</p>
</div>

<table class="w-full border-collapse border border-black">
    <thead>
        <tr class="bg-[#0B9E4F] text-white" style="background-color: #0B9E4F !important; color: white !important;">
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="2">MONTH</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="2">Ending Balance<br>Last Month<br>(g Pollens)</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" colspan="3">POLLENS RECEIVED FROM OTHER CENTER</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" colspan="6">POLLEN UTILIZATION (grams of Pollen) per Week</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="2">Ending Balance<br>(g Pollens)</th>
        </tr>
        <tr class="bg-[#0B9E4F] text-white" style="background-color: #0B9E4F !important; color: white !important;">
            <th class="border border-black px-1 py-0.5 font-semibold text-center w-24">Source</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center w-24">Date Received<br>mm/dd/yyyy</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center w-20">Grams of<br>Pollens</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center w-16">Week 1</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center w-16">Week 2</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center w-16">Week 3</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center w-16">Week 4</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center w-16">Week 5</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center w-20">TOTAL</th>
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
                <td class="border border-black px-1 py-0.5 text-center">{{ $rec->month_label }}</td>
                <td class="border border-black px-1 py-0.5 text-right">{{ number_format($rec->ending_balance_prev, 2) }} g</td>
                <td class="border border-black px-1 py-0.5">{{ $rec->pollen_source }}</td>
                <td class="border border-black px-1 py-0.5 text-center">{{ $rec->date_received ? \Carbon\Carbon::parse($rec->date_received)->format('m/d/Y') : '' }}</td>
                <td class="border border-black px-1 py-0.5 text-right">{{ number_format($rec->pollens_received, 2) }} g</td>
                <td class="border border-black px-1 py-0.5 text-right">{{ number_format($rec->week1, 2) }} g</td>
                <td class="border border-black px-1 py-0.5 text-right">{{ number_format($rec->week2, 2) }} g</td>
                <td class="border border-black px-1 py-0.5 text-right">{{ number_format($rec->week3, 2) }} g</td>
                <td class="border border-black px-1 py-0.5 text-right">{{ number_format($rec->week4, 2) }} g</td>
                <td class="border border-black px-1 py-0.5 text-right">{{ number_format($rec->week5, 2) }} g</td>
                <td class="border border-black px-1 py-0.5 text-right font-semibold">{{ number_format($utilTotal, 2) }} g</td>
                <td class="border border-black px-1 py-0.5 text-right font-bold">{{ number_format($endBalance, 2) }} g</td>
            </tr>
        @endforeach
        
        <tr class="font-bold bg-gray-100">
            <td class="border border-black px-1 py-0.5 text-right" colspan="4">TOTAL:</td>
            <td class="border border-black px-1 py-0.5 text-right">{{ number_format($totalReceived, 2) }} g</td>
            <td class="border border-black px-1 py-0.5" colspan="5"></td>
            <td class="border border-black px-1 py-0.5 text-right">{{ number_format($totalUtil, 2) }} g</td>
            <td class="border border-black px-1 py-0.5"></td>
        </tr>
    </tbody>
</table>
