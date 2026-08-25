<table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 7px; line-height: 1.2;">
    <thead>
        <tr style="background-color: #0B9E4F; color: white;">
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="3">Region</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="3">Province</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="3">District</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="3">Municipality</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="3">Barangay</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" colspan="5">Name of Farmer Participant</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" colspan="3">Farm Location</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="3">Seedlings Received</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="3">Date Received</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="3">Type/Variety</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="3">No. of Seedlings Planted</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="3">Date Planted</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center; font-size: 6px;" rowspan="3">REMARKS</th>
        </tr>
        <tr style="background-color: #0B9E4F; color: white;">
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="2">Family Name</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="2">Given Name</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="2">M.I.</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" colspan="2">Gender</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="2">Barangay</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="2">Municipality</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;" rowspan="2">Province</th>
        </tr>
        <tr style="background-color: #0B9E4F; color: white;">
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;">Male</th>
            <th style="border: 1px solid #000; padding: 2px 3px; font-weight: 600; text-align: center;">Female</th>
        </tr>
    </thead>
    <tbody>
        @php
            $siteName = $reportData->first()->fieldSite?->name ?? 'UNKNOWN SITE';
        @endphp
        <tr>
            <td colspan="19" style="border: 1px solid #000; padding: 2px 3px; text-align: center; font-weight: bold; font-size: 9px; background-color: #f9fafb;">BOHOL PROVINCE</td>
        </tr>
        <tr>
            <td colspan="19" style="border: 1px solid #000; padding: 2px 3px; text-align: center; font-weight: bold; font-size: 9px; background-color: #f9fafb;">COMMUNAL NURSERY AT {{ strtoupper($siteName) }}</td>
        </tr>

        @php
            $totalPlanted = 0;
            $totalReceived = 0;
        @endphp

        @foreach($reportData as $rec)
            @php
                $totalPlanted += (int)$rec->seedlings_planted;
                $totalReceived += (int)str_replace(',', '', $rec->seedlings_received ?? '0');
            @endphp
            <tr>
                <td style="border: 1px solid #000; padding: 2px 3px;">{{ $rec->region }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px;">{{ $rec->province }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px;">{{ $rec->district }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px;">{{ $rec->municipality }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px;">{{ $rec->barangay }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px;">{{ $rec->farmer_last_name }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px;">{{ $rec->farmer_first_name }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px; text-align: center;">{{ $rec->farmer_middle_initial }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px; text-align: center; font-weight: bold;">{{ $rec->is_male ? '/' : '' }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px; text-align: center; font-weight: bold;">{{ $rec->is_female ? '/' : '' }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px;">{{ $rec->farm_barangay }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px;">{{ $rec->farm_municipality }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px;">{{ $rec->farm_province ?? 'Bohol' }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px; text-align: center;">{{ $rec->seedlings_received }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px; text-align: center;">{{ $rec->date_received ? \Carbon\Carbon::parse($rec->date_received)->format('m/d/Y') : '' }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px; text-align: center;">{{ $rec->variety }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px; text-align: center;">{{ $rec->seedlings_planted }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px; text-align: center;">{{ $rec->date_planted ? \Carbon\Carbon::parse($rec->date_planted)->format('m/d/Y') : '' }}</td>
                <td style="border: 1px solid #000; padding: 2px 3px; font-size: 6px; word-wrap: break-word; overflow: hidden;">{{ $rec->remarks }}</td>
            </tr>
        @endforeach

        <tr style="font-weight: bold; background-color: #f3f4f6;">
            <td colspan="13" style="border: 1px solid #000; padding: 2px 3px; text-align: right;">TOTAL:</td>
            <td style="border: 1px solid #000; padding: 2px 3px; text-align: center;">{{ $totalReceived > 0 ? $totalReceived : '' }}</td>
            <td colspan="2" style="border: 1px solid #000; padding: 2px 3px;"></td>
            <td style="border: 1px solid #000; padding: 2px 3px; text-align: center;">{{ $totalPlanted > 0 ? $totalPlanted : '' }}</td>
            <td colspan="2" style="border: 1px solid #000; padding: 2px 3px;"></td>
        </tr>
    </tbody>
</table>
