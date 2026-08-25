<table class="w-full border-collapse border border-black text-[7.5px] leading-tight">
    <thead>
        <tr class="bg-[#0B9E4F] text-white" style="background-color: #0B9E4F !important; color: white !important;">
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-20">Region/Prov/Dist</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-20">Barangay/Muni</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-20">Entity Name</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-20">Rep</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-9 rotated-header">Target Seednuts</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-9 rotated-header">Seednuts Harvested</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-14 rotated-header">Date Harvested</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-14 rotated-header">Date Received</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-20">Source</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-16">Variety</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-9 rotated-header">Seednuts Sown</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-14 rotated-header">Date Sown</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-9 rotated-header">Germinated</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-9 rotated-header">Ungerminated</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-9 rotated-header">Culled</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-9 rotated-header">Good (1ft)</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-9 rotated-header">Ready to Plant</th>
            <th class="border border-black px-0.5 py-0 font-semibold text-center w-9 rotated-header">Dispatched</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandTotals = array_fill(0, 18, 0);
        @endphp

        @foreach($reportData as $rec)
            @php
                $batches = collect($rec->batches ?? []);
            @endphp
            
            @if($batches->isEmpty())
                <tr>
                    <td class="border border-black px-0.5 py-0">{{ $rec->region_province_district }}</td>
                    <td class="border border-black px-0.5 py-0">{{ $rec->barangay_municipality }}</td>
                    <td class="border border-black px-0.5 py-0">{{ $rec->proponent_entity }}</td>
                    <td class="border border-black px-0.5 py-0">{{ $rec->proponent_representative }}</td>
                    <td class="border border-black px-0.5 py-0 text-center">{{ $rec->target_seednuts }}</td>
                    <td colspan="13" class="border border-black px-0.5 py-0"></td>
                </tr>
                @php $grandTotals[4] += (int)$rec->target_seednuts; @endphp
            @else
                @php $firstBatch = true; $recRows = $batches->sum(fn($b) => max(1, $b->varieties->count())); @endphp
                @foreach($batches as $batch)
                    @php $varieties = collect($batch->varieties ?? []); @endphp
                    @if($varieties->isEmpty())
                        <tr>
                            @if($firstBatch)
                                <td class="border border-black px-0.5 py-0" rowspan="{{ $recRows }}">{{ $rec->region_province_district }}</td>
                                <td class="border border-black px-0.5 py-0" rowspan="{{ $recRows }}">{{ $rec->barangay_municipality }}</td>
                                <td class="border border-black px-0.5 py-0" rowspan="{{ $recRows }}">{{ $rec->proponent_entity }}</td>
                                <td class="border border-black px-0.5 py-0" rowspan="{{ $recRows }}">{{ $rec->proponent_representative }}</td>
                                <td class="border border-black px-0.5 py-0 text-center" rowspan="{{ $recRows }}">{{ $rec->target_seednuts }}</td>
                                @php $firstBatch = false; $grandTotals[4] += (int)$rec->target_seednuts; @endphp
                            @endif
                            <td class="border border-black px-0.5 py-0 text-center">{{ $batch->seednuts_harvested }}</td>
                            <td class="border border-black px-0.5 py-0 text-center">{{ $batch->date_harvested ? \Carbon\Carbon::parse($batch->date_harvested)->format('m/d/Y') : '' }}</td>
                            <td class="border border-black px-0.5 py-0 text-center">{{ $batch->date_received ? \Carbon\Carbon::parse($batch->date_received)->format('m/d/Y') : '' }}</td>
                            <td class="border border-black px-0.5 py-0">{{ $batch->source_of_seednuts }}</td>
                            <td colspan="9" class="border border-black px-0.5 py-0"></td>
                        </tr>
                        @php $grandTotals[5] += (int)$batch->seednuts_harvested; @endphp
                    @else
                        @php $firstVar = true; $batchRows = $varieties->count(); @endphp
                        @foreach($varieties as $v)
                            <tr>
                                @if($firstBatch)
                                    <td class="border border-black px-0.5 py-0" rowspan="{{ $recRows }}">{{ $rec->region_province_district }}</td>
                                    <td class="border border-black px-0.5 py-0" rowspan="{{ $recRows }}">{{ $rec->barangay_municipality }}</td>
                                    <td class="border border-black px-0.5 py-0" rowspan="{{ $recRows }}">{{ $rec->proponent_entity }}</td>
                                    <td class="border border-black px-0.5 py-0" rowspan="{{ $recRows }}">{{ $rec->proponent_representative }}</td>
                                    <td class="border border-black px-0.5 py-0 text-center" rowspan="{{ $recRows }}">{{ $rec->target_seednuts }}</td>
                                    @php $firstBatch = false; $grandTotals[4] += (int)$rec->target_seednuts; @endphp
                                @endif

                                @if($firstVar)
                                    <td class="border border-black px-0.5 py-0 text-center" rowspan="{{ $batchRows }}">{{ $batch->seednuts_harvested }}</td>
                                    <td class="border border-black px-0.5 py-0 text-center" rowspan="{{ $batchRows }}">{{ $batch->date_harvested ? \Carbon\Carbon::parse($batch->date_harvested)->format('m/d/Y') : '' }}</td>
                                    <td class="border border-black px-0.5 py-0 text-center" rowspan="{{ $batchRows }}">{{ $batch->date_received ? \Carbon\Carbon::parse($batch->date_received)->format('m/d/Y') : '' }}</td>
                                    <td class="border border-black px-0.5 py-0" rowspan="{{ $batchRows }}">{{ $batch->source_of_seednuts }}</td>
                                    @php $firstVar = false; $grandTotals[5] += (int)$batch->seednuts_harvested; @endphp
                                @endif

                                <td class="border border-black px-0.5 py-0">{{ $v->variety }}</td>
                                <td class="border border-black px-0.5 py-0 text-center">{{ $v->sown_seednuts }}</td>
                                <td class="border border-black px-0.5 py-0 text-center">{{ $v->date_sown ? \Carbon\Carbon::parse($v->date_sown)->format('m/d/Y') : '' }}</td>
                                <td class="border border-black px-0.5 py-0 text-center">{{ $v->germinated_seedlings }}</td>
                                <td class="border border-black px-0.5 py-0 text-center">{{ $v->ungerminated_seednuts }}</td>
                                <td class="border border-black px-0.5 py-0 text-center">{{ $v->culled_seedlings }}</td>
                                <td class="border border-black px-0.5 py-0 text-center">{{ $v->good_seedlings }}</td>
                                <td class="border border-black px-0.5 py-0 text-center">{{ $v->ready_to_plant }}</td>
                                <td class="border border-black px-0.5 py-0 text-center">{{ $v->dispatched_seedlings }}</td>
                            </tr>
                            @php
                                $grandTotals[10] += (int)$v->sown_seednuts;
                                $grandTotals[12] += (int)$v->germinated_seedlings;
                                $grandTotals[13] += (int)$v->ungerminated_seednuts;
                                $grandTotals[14] += (int)$v->culled_seedlings;
                                $grandTotals[15] += (int)$v->good_seedlings;
                                $grandTotals[16] += (int)$v->ready_to_plant;
                                $grandTotals[17] += (int)$v->dispatched_seedlings;
                            @endphp
                        @endforeach
                    @endif
                @endforeach
            @endif
        @endforeach

        <tr class="font-bold bg-gray-100">
            <td colspan="4" class="border border-black px-0.5 py-0 text-right">TOTAL:</td>
            <td class="border border-black px-0.5 py-0 text-center">{{ $grandTotals[4] > 0 ? $grandTotals[4] : '' }}</td>
            <td class="border border-black px-0.5 py-0 text-center">{{ $grandTotals[5] > 0 ? $grandTotals[5] : '' }}</td>
            <td colspan="4" class="border border-black px-0.5 py-0"></td>
            <td class="border border-black px-0.5 py-0 text-center">{{ $grandTotals[10] > 0 ? $grandTotals[10] : '' }}</td>
            <td class="border border-black px-0.5 py-0"></td>
            <td class="border border-black px-0.5 py-0 text-center">{{ $grandTotals[12] > 0 ? $grandTotals[12] : '' }}</td>
            <td class="border border-black px-0.5 py-0 text-center">{{ $grandTotals[13] > 0 ? $grandTotals[13] : '' }}</td>
            <td class="border border-black px-0.5 py-0 text-center">{{ $grandTotals[14] > 0 ? $grandTotals[14] : '' }}</td>
            <td class="border border-black px-0.5 py-0 text-center">{{ $grandTotals[15] > 0 ? $grandTotals[15] : '' }}</td>
            <td class="border border-black px-0.5 py-0 text-center">{{ $grandTotals[16] > 0 ? $grandTotals[16] : '' }}</td>
            <td class="border border-black px-0.5 py-0 text-center">{{ $grandTotals[17] > 0 ? $grandTotals[17] : '' }}</td>
        </tr>
    </tbody>
</table>
