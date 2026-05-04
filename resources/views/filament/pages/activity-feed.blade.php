<x-filament-panels::page>
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
        {{-- Accent gradient strip --}}
        <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #0B9E4F 0%, #10B981 50%, #34D399 100%);"></div>

        <div class="p-6">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl" style="background: linear-gradient(135deg, #0B9E4F, #10B981);">
                        <x-heroicon-o-queue-list class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Activity Feed Log</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Track all operations and updates across the system</p>
                    </div>
                </div>
                
                <div class="w-64">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="filterType">
                            <option value="all">All Activities</option>
                            <option value="harvest">Harvests</option>
                            <option value="nursery">Nursery Data</option>
                            <option value="distribution">Distributions</option>
                            <option value="pollen">Pollen Extraction</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="space-y-6 px-1 max-w-4xl">
                @forelse ($this->activities as $activity)
                    <div class="relative pl-4 border-l-2 @if($activity['color'] == 'success') border-green-500 @elseif($activity['color'] == 'primary') border-blue-500 @elseif($activity['color'] == 'info') border-purple-500 @elseif($activity['color'] == 'warning') border-orange-500 @else border-gray-300 @endif pb-4">
                        <div class="flex justify-between items-start mb-1">
                            @php
                                $badgeColor = match($activity['color']) {
                                    'success' => 'success',
                                    'primary' => 'primary',
                                    'info' => 'info',
                                    'warning' => 'warning',
                                    'danger' => 'danger',
                                    default => 'gray',
                                };
                            @endphp
                            <div class="flex items-center gap-3">
                                <x-filament::badge :color="$badgeColor" size="sm" class="mb-1">
                                    {{ $activity['type'] }}
                                </x-filament::badge>
                                
                                <h3 class="text-md font-bold text-gray-800 dark:text-gray-100">
                                    {{ $activity['title'] }}
                                </h3>
                            </div>
                            
                            <span class="text-xs uppercase font-bold text-gray-400">
                                {{ $activity['date']->format('M d, Y h:i A') }} ({{ $activity['date']->diffForHumans() }})
                            </span>
                        </div>
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mb-2 italic">
                            {{ $activity['desc'] }}
                        </p>
                        
                        <div class="flex items-center gap-1 text-gray-400">
                            <x-filament::icon icon="heroicon-m-user" class="h-4 w-4" />
                            <span class="text-xs font-medium">{{ $activity['user'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                        <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="h-12 w-12 mb-4 text-gray-400" />
                        <span class="text-lg font-medium">No activities found</span>
                        <span class="text-sm">Try changing your filters or check back later.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
