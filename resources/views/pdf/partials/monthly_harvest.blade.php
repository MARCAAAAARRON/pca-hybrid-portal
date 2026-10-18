<table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 8px; line-height: 1.2;">
    <thead>
        <tr style="border: none !important;">
            <th colspan="21" style="border: none !important; font-weight: normal; padding: 0 0 10px 0; background: transparent !important; text-align: center;">
                @include('pdf.partials.pca_header')
            </th>
        </tr>
        <tr style="background-color: #f3f4f6; color: black;">
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600;" rowspan="2">Farm Location</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600;" rowspan="2">Name of Partner</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600;" rowspan="2">Area (Ha.)</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600;" rowspan="2">Age of Palms (Years)</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600;" rowspan="2">No. of Hybridized Palms</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600;" rowspan="2">Variety / Hybrid Crosses</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600;" rowspan="2">Seednuts Produced</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center;" colspan="12">Monthly Production (No. of Seednuts)</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600;" rowspan="2">TOTAL</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600;" rowspan="2">Remarks</th>
        </tr>
        <tr style="background-color: #f3f4f6; color: black;">
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">Jan</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">Feb</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">Mar</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">Apr</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">May</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">Jun</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">Jul</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">Aug</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">Sep</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">Oct</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">Nov</th>
            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: 600; text-align: center; width: 30px;">Dec</th>
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
                        <td style="border: 1px solid #000; padding: 2px 4px;" rowspan="{{ $varCount }}">{{ $farm['location'] }}</td>
                        <td style="border: 1px solid #000; padding: 2px 4px;" rowspan="{{ $varCount }}">{{ $farm['farm_name'] }}</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;" rowspan="{{ $varCount }}">{{ $farm['area_ha'] }}</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;" rowspan="{{ $varCount }}">{{ $farm['age_of_palms'] }}</td>
                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;" rowspan="{{ $varCount }}">{{ $farm['num_hybridized_palms'] }}</td>
                        @php $firstVar = false; @endphp
                    @endif
                    
                    <td style="border: 1px solid #000; padding: 2px 4px;">{{ $v['variety'] }}</td>
                    <td style="border: 1px solid #000; padding: 2px 4px;">{{ $v['type'] }}</td>
                    
                    @php $rowTotal = 0; @endphp
                    @for($m = 1; $m <= 12; $m++)
                        @php 
                            $count = $v['months'][$m]; 
                            $rowTotal += $count;
                            $grandTotals[$m] += $count;
                        @endphp
                        <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ $count > 0 ? $count : '' }}</td>
                    @endfor
                    
                    @php $grandTotalSum += $rowTotal; @endphp
                    <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: 600;">{{ $rowTotal > 0 ? $rowTotal : '' }}</td>
                    <td style="border: 1px solid #000; padding: 2px 4px;">{{ $v['remarks'] }}</td>
                </tr>
            @endforeach
        @endforeach

        <!-- TOTAL ROW -->
        <tr style="font-weight: bold; background-color: #f3f4f6;">
            <td style="border: 1px solid #000; padding: 2px 4px; text-align: right;" colspan="2">TOTAL</td>
            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ $totalArea > 0 ? $totalArea : '' }}</td>
            <td style="border: 1px solid #000; padding: 2px 4px;"></td>
            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ $totalPalms > 0 ? $totalPalms : '' }}</td>
            <td style="border: 1px solid #000; padding: 2px 4px;" colspan="2"></td>
            @for($m = 1; $m <= 12; $m++)
                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ $grandTotals[$m] > 0 ? $grandTotals[$m] : '' }}</td>
            @endfor
            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ $grandTotalSum > 0 ? $grandTotalSum : '' }}</td>
            <td style="border: 1px solid #000; padding: 2px 4px;"></td>
        </tr>
    </tbody>
</table>
