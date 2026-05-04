<table class="w-full border-collapse border border-black">
    <thead>
        <tr class="bg-[#0B9E4F] text-white" style="background-color: #0B9E4F !important; color: white !important;">
            <th class="border border-black px-1 py-0.5 font-semibold" rowspan="2">Farm Location</th>
            <th class="border border-black px-1 py-0.5 font-semibold" rowspan="2">Name of Partner</th>
            <th class="border border-black px-1 py-0.5 font-semibold" rowspan="2">Area (Ha.)</th>
            <th class="border border-black px-1 py-0.5 font-semibold" rowspan="2">Age of Palms (Years)</th>
            <th class="border border-black px-1 py-0.5 font-semibold" rowspan="2">No. of Hybridized Palms</th>
            <th class="border border-black px-1 py-0.5 font-semibold" rowspan="2">Variety / Hybrid Crosses</th>
            <th class="border border-black px-1 py-0.5 font-semibold" rowspan="2">Seednuts Produced</th>
            <th class="border border-black px-1 py-0.5 font-semibold" colspan="12">Monthly Production (No. of Seednuts)</th>
            <th class="border border-black px-1 py-0.5 font-semibold" rowspan="2">TOTAL</th>
            <th class="border border-black px-1 py-0.5 font-semibold" rowspan="2">Remarks</th>
        </tr>
        <tr class="bg-[#0B9E4F] text-white" style="background-color: #0B9E4F !important; color: white !important;">
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">Jan</th>
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">Feb</th>
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">Mar</th>
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">Apr</th>
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">May</th>
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">Jun</th>
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">Jul</th>
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">Aug</th>
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">Sep</th>
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">Oct</th>
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">Nov</th>
            <th class="border border-black px-1 py-0.5 font-semibold w-12 text-center">Dec</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandTotals = array_fill(1, 12, 0);
            $grandTotalSum = 0;
            $totalArea = 0;
            $totalPalms = 0;
        @endphp

        @foreach($reportFarms as $farmKey => $farm)
            @php 
                $varCount = count($farm['varieties']); 
                $firstVar = true;
                $totalArea += floatval($farm['area_ha']);
                $totalPalms += intval($farm['num_hybridized_palms']);
            @endphp
            @foreach($farm['varieties'] as $vKey => $v)
                <tr>
                    @if($firstVar)
                        <td class="border border-black px-1 py-0.5" rowspan="{{ $varCount }}">{{ $farm['location'] }}</td>
                        <td class="border border-black px-1 py-0.5" rowspan="{{ $varCount }}">{{ $farm['farm_name'] }}</td>
                        <td class="border border-black px-1 py-0.5 text-center" rowspan="{{ $varCount }}">{{ $farm['area_ha'] }}</td>
                        <td class="border border-black px-1 py-0.5 text-center" rowspan="{{ $varCount }}">{{ $farm['age_of_palms'] }}</td>
                        <td class="border border-black px-1 py-0.5 text-center" rowspan="{{ $varCount }}">{{ $farm['num_hybridized_palms'] }}</td>
                        @php $firstVar = false; @endphp
                    @endif
                    
                    <td class="border border-black px-1 py-0.5">{{ $v['variety'] }}</td>
                    <td class="border border-black px-1 py-0.5">{{ $v['type'] }}</td>
                    
                    @php $rowTotal = 0; @endphp
                    @for($m = 1; $m <= 12; $m++)
                        @php 
                            $count = $v['months'][$m]; 
                            $rowTotal += $count;
                            $grandTotals[$m] += $count;
                        @endphp
                        <td class="border border-black px-1 py-0.5 text-center">{{ $count > 0 ? $count : '' }}</td>
                    @endfor
                    
                    @php $grandTotalSum += $rowTotal; @endphp
                    <td class="border border-black px-1 py-0.5 text-center font-semibold">{{ $rowTotal > 0 ? $rowTotal : '' }}</td>
                    <td class="border border-black px-1 py-0.5">{{ $v['remarks'] }}</td>
                </tr>
            @endforeach
        @endforeach

        <!-- TOTAL ROW -->
        <tr class="font-bold bg-gray-100">
            <td class="border border-black px-1 py-0.5 text-right" colspan="2">TOTAL</td>
            <td class="border border-black px-1 py-0.5 text-center">{{ $totalArea > 0 ? $totalArea : '' }}</td>
            <td class="border border-black px-1 py-0.5"></td>
            <td class="border border-black px-1 py-0.5 text-center">{{ $totalPalms > 0 ? $totalPalms : '' }}</td>
            <td class="border border-black px-1 py-0.5" colspan="2"></td>
            @for($m = 1; $m <= 12; $m++)
                <td class="border border-black px-1 py-0.5 text-center">{{ $grandTotals[$m] > 0 ? $grandTotals[$m] : '' }}</td>
            @endfor
            <td class="border border-black px-1 py-0.5 text-center">{{ $grandTotalSum > 0 ? $grandTotalSum : '' }}</td>
            <td class="border border-black px-1 py-0.5"></td>
        </tr>
    </tbody>
</table>
