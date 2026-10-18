<div class="flex justify-center items-center gap-4 mb-3">
    <img src="{{ asset('images/PCA_DA_Logo.png') }}" class="h-16 w-16 object-contain" alt="PCA Logo">
    <div class="text-center">
        <h2 class="font-bold text-sm leading-tight text-black">PHILIPPINE COCONUT AUTHORITY</h2>
        <p class="font-bold text-xs uppercase text-black">COCONUT HYBRIDIZATION PROJECT-CFIDP</p>
        @if($currentCategory === 'monthly_harvest')
            <p class="uppercase text-xs mt-0.5 text-black">ON-FARM HYBRID SEEDNUT PRODUCTION</p>
        @elseif($currentCategory === 'pollen_production')
            <p class="uppercase text-xs mt-0.5 text-black">POLLEN PRODUCTION AND PROCESSING</p>
        @elseif($currentCategory === 'hybrid_distribution')
            <p class="uppercase text-xs mt-0.5 text-black">DISTRIBUTION OF HYBRID SEEDLINGS</p>
        @elseif($currentCategory === 'nursery_operation')
            <p class="uppercase text-xs mt-0.5 text-black">MONTHLY NURSERY OPERATION REPORT</p>
        @elseif($currentCategory === 'terminal_report')
            <p class="uppercase text-xs mt-0.5 text-black">TERMINAL REPORT FOR NURSERY OPERATIONS</p>
        @endif
        <p class="text-xs font-semibold underline mt-1 text-black">{{ $asOfStr }}</p>
    </div>
</div>
