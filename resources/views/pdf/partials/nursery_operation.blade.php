<table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 6.5px; line-height: 1.1;">
    <thead>
        <tr style="background-color: #0B9E4F; color: white;">
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Region/Prov/Dist</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Barangay/Muni</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Entity Name</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Rep</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Target Seednuts</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Seednuts Harvested</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Date Harvested</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Date Received</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Source</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Variety</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Seednuts Sown</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Date Sown</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Germinated</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Ungerminated</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Culled</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Good (1ft)</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Ready to Plant</th>
            <th style="border: 1px solid #000; padding: 1px 3px; font-weight: 600; text-align: center;">Dispatched</th>
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
                    <td style="border: 1px solid #000; padding: 1px 3px;">{{ $rec->region_province_district }}</td>
                    <td style="border: 1px solid #000; padding: 1px 3px;">{{ $rec->barangay_municipality }}</td>
                    <td style="border: 1px solid #000; padding: 1px 3px;">{{ $rec->proponent_entity }}</td>
                    <td style="border: 1px solid #000; padding: 1px 3px;">{{ $rec->proponent_representative }}</td>
                    <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $rec->target_seednuts }}</td>
                    <td colspan="13" style="border: 1px solid #000; padding: 1px 3px;"></td>
                </tr>
                @php $grandTotals[4] += (int)$rec->target_seednuts; @endphp
            @else
                @php $firstBatch = true; $recRows = $batches->sum(fn($b) => max(1, $b->varieties->count())); @endphp
                @foreach($batches as $batch)
                    @php $varieties = collect($batch->varieties ?? []); @endphp
                    @if($varieties->isEmpty())
                        <tr>
                            @if($firstBatch)
                                <td style="border: 1px solid #000; padding: 1px 3px;" rowspan="{{ $recRows }}">{{ $rec->region_province_district }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px;" rowspan="{{ $recRows }}">{{ $rec->barangay_municipality }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px;" rowspan="{{ $recRows }}">{{ $rec->proponent_entity }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px;" rowspan="{{ $recRows }}">{{ $rec->proponent_representative }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;" rowspan="{{ $recRows }}">{{ $rec->target_seednuts }}</td>
                                @php $firstBatch = false; $grandTotals[4] += (int)$rec->target_seednuts; @endphp
                            @endif
                            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $batch->seednuts_harvested }}</td>
                            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $batch->date_harvested ? \Carbon\Carbon::parse($batch->date_harvested)->format('m/d/Y') : '' }}</td>
                            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $batch->date_received ? \Carbon\Carbon::parse($batch->date_received)->format('m/d/Y') : '' }}</td>
                            <td style="border: 1px solid #000; padding: 1px 3px;">{{ $batch->source_of_seednuts }}</td>
                            <td colspan="9" style="border: 1px solid #000; padding: 1px 3px;"></td>
                        </tr>
                        @php $grandTotals[5] += (int)$batch->seednuts_harvested; @endphp
                    @else
                        @php $firstVar = true; $batchRows = $varieties->count(); @endphp
                        @foreach($varieties as $v)
                            <tr>
                                @if($firstBatch)
                                    <td style="border: 1px solid #000; padding: 1px 3px;" rowspan="{{ $recRows }}">{{ $rec->region_province_district }}</td>
                                    <td style="border: 1px solid #000; padding: 1px 3px;" rowspan="{{ $recRows }}">{{ $rec->barangay_municipality }}</td>
                                    <td style="border: 1px solid #000; padding: 1px 3px;" rowspan="{{ $recRows }}">{{ $rec->proponent_entity }}</td>
                                    <td style="border: 1px solid #000; padding: 1px 3px;" rowspan="{{ $recRows }}">{{ $rec->proponent_representative }}</td>
                                    <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;" rowspan="{{ $recRows }}">{{ $rec->target_seednuts }}</td>
                                    @php $firstBatch = false; $grandTotals[4] += (int)$rec->target_seednuts; @endphp
                                @endif

                                @if($firstVar)
                                    <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;" rowspan="{{ $batchRows }}">{{ $batch->seednuts_harvested }}</td>
                                    <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;" rowspan="{{ $batchRows }}">{{ $batch->date_harvested ? \Carbon\Carbon::parse($batch->date_harvested)->format('m/d/Y') : '' }}</td>
                                    <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;" rowspan="{{ $batchRows }}">{{ $batch->date_received ? \Carbon\Carbon::parse($batch->date_received)->format('m/d/Y') : '' }}</td>
                                    <td style="border: 1px solid #000; padding: 1px 3px;" rowspan="{{ $batchRows }}">{{ $batch->source_of_seednuts }}</td>
                                    @php $firstVar = false; $grandTotals[5] += (int)$batch->seednuts_harvested; @endphp
                                @endif

                                <td style="border: 1px solid #000; padding: 1px 3px;">{{ $v->variety }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $v->sown_seednuts }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $v->date_sown ? \Carbon\Carbon::parse($v->date_sown)->format('m/d/Y') : '' }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $v->germinated_seedlings }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $v->ungerminated_seednuts }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $v->culled_seedlings }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $v->good_seedlings }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $v->ready_to_plant }}</td>
                                <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $v->dispatched_seedlings }}</td>
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

        <tr style="font-weight: bold; background-color: #f3f4f6;">
            <td colspan="4" style="border: 1px solid #000; padding: 1px 3px; text-align: right;">TOTAL:</td>
            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $grandTotals[4] > 0 ? $grandTotals[4] : '' }}</td>
            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $grandTotals[5] > 0 ? $grandTotals[5] : '' }}</td>
            <td colspan="4" style="border: 1px solid #000; padding: 1px 3px;"></td>
            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $grandTotals[10] > 0 ? $grandTotals[10] : '' }}</td>
            <td style="border: 1px solid #000; padding: 1px 3px;"></td>
            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $grandTotals[12] > 0 ? $grandTotals[12] : '' }}</td>
            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $grandTotals[13] > 0 ? $grandTotals[13] : '' }}</td>
            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $grandTotals[14] > 0 ? $grandTotals[14] : '' }}</td>
            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $grandTotals[15] > 0 ? $grandTotals[15] : '' }}</td>
            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $grandTotals[16] > 0 ? $grandTotals[16] : '' }}</td>
            <td style="border: 1px solid #000; padding: 1px 3px; text-align: center;">{{ $grandTotals[17] > 0 ? $grandTotals[17] : '' }}</td>
        </tr>
    </tbody>
</table>
