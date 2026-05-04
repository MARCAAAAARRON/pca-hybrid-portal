<table class="w-full border-collapse border border-black text-xs">
    <thead>
        <tr class="bg-[#0B9E4F] text-white" style="background-color: #0B9E4F !important; color: white !important;">
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="3">Region</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="3">Province</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="3">District</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="3">Municipality</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="3">Barangay</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" colspan="5">Name of Farmer Participant</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" colspan="3">Farm Location</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="3">Seedlings Received</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="3">Date Received</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="3">Type/Variety</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="3">No. of Seedlings Planted</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="3">Date Planted</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="3">REMARKS</th>
        </tr>
        <tr class="bg-[#0B9E4F] text-white" style="background-color: #0B9E4F !important; color: white !important;">
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="2">Family Name</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="2">Given Name</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="2">M.I.</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" colspan="2">Gender</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="2">Barangay</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="2">Municipality</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center" rowspan="2">Province</th>
        </tr>
        <tr class="bg-[#0B9E4F] text-white" style="background-color: #0B9E4F !important; color: white !important;">
            <th class="border border-black px-1 py-0.5 font-semibold text-center">Male</th>
            <th class="border border-black px-1 py-0.5 font-semibold text-center">Female</th>
        </tr>
    </thead>
    <tbody>
        @php
            $siteName = $reportData->first()->fieldSite?->name ?? 'UNKNOWN SITE';
        @endphp
        <tr>
            <td colspan="19" class="border border-black px-1 py-0.5 text-center font-bold text-sm bg-gray-50">BOHOL PROVINCE</td>
        </tr>
        <tr>
            <td colspan="19" class="border border-black px-1 py-0.5 text-center font-bold text-sm bg-gray-50">COMMUNAL NURSERY AT {{ strtoupper($siteName) }}</td>
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
                <td class="border border-black px-1 py-0.5">{{ $rec->region }}</td>
                <td class="border border-black px-1 py-0.5">{{ $rec->province }}</td>
                <td class="border border-black px-1 py-0.5">{{ $rec->district }}</td>
                <td class="border border-black px-1 py-0.5">{{ $rec->municipality }}</td>
                <td class="border border-black px-1 py-0.5">{{ $rec->barangay }}</td>
                <td class="border border-black px-1 py-0.5">{{ $rec->farmer_last_name }}</td>
                <td class="border border-black px-1 py-0.5">{{ $rec->farmer_first_name }}</td>
                <td class="border border-black px-1 py-0.5 text-center">{{ $rec->farmer_middle_initial }}</td>
                <td class="border border-black px-1 py-0.5 text-center font-bold">{{ ($rec->gender ?? '') === 'M' ? '/' : '' }}</td>
                <td class="border border-black px-1 py-0.5 text-center font-bold">{{ ($rec->gender ?? '') === 'F' ? '/' : '' }}</td>
                <td class="border border-black px-1 py-0.5">{{ $rec->farm_barangay }}</td>
                <td class="border border-black px-1 py-0.5">{{ $rec->farm_municipality }}</td>
                <td class="border border-black px-1 py-0.5">{{ $rec->farm_province ?? 'Bohol' }}</td>
                <td class="border border-black px-1 py-0.5 text-center">{{ $rec->seedlings_received }}</td>
                <td class="border border-black px-1 py-0.5 text-center">{{ $rec->date_received ? \Carbon\Carbon::parse($rec->date_received)->format('m/d/Y') : '' }}</td>
                <td class="border border-black px-1 py-0.5 text-center">{{ $rec->variety }}</td>
                <td class="border border-black px-1 py-0.5 text-center">{{ $rec->seedlings_planted }}</td>
                <td class="border border-black px-1 py-0.5 text-center">{{ $rec->date_planted ? \Carbon\Carbon::parse($rec->date_planted)->format('m/d/Y') : '' }}</td>
                <td class="border border-black px-1 py-0.5">{{ $rec->remarks }}</td>
            </tr>
        @endforeach

        <tr class="font-bold bg-gray-100 border-t-2 border-t-black">
            <td colspan="13" class="border border-black px-1 py-0.5 text-right">TOTAL:</td>
            <td class="border border-black px-1 py-0.5 text-center">{{ $totalReceived > 0 ? $totalReceived : '' }}</td>
            <td colspan="2" class="border border-black px-1 py-0.5"></td>
            <td class="border border-black px-1 py-0.5 text-center">{{ $totalPlanted > 0 ? $totalPlanted : '' }}</td>
            <td colspan="2" class="border border-black px-1 py-0.5"></td>
        </tr>
    </tbody>
</table>
