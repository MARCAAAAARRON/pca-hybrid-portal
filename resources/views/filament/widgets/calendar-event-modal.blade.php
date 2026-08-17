<div class="space-y-4">
    @if(!empty($event['details']))
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($event['details'] as $label => $value)
                <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ $label }}</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500 dark:text-gray-400">No additional details available.</p>
    @endif

    @if(!empty($event['record_url']))
        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
            <x-filament::button tag="a" href="{{ $event['record_url'] }}" color="primary" icon="heroicon-m-arrow-top-right-on-square">
                View Full Record
            </x-filament::button>
        </div>
    @endif
</div>
