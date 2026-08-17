<x-filament-widgets::widget>
    <x-filament::section shadow="sm" border="true">
        <div class="flex items-center gap-2 mb-6">
            <x-filament::icon icon="heroicon-m-arrow-path" class="h-5 w-5 text-gray-500" />
            <h2 class="text-base font-bold leading-6 text-gray-950 dark:text-white">
                Recent Activity Feed
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-1">
            @forelse ($this->getActivities() as $activity)
                <div
                    class="relative pl-3 border-l-2 @if($activity['color'] == 'success') border-green-500 @elseif($activity['color'] == 'primary') border-blue-500 @elseif($activity['color'] == 'info') border-purple-500 @elseif($activity['color'] == 'warning') border-orange-500 @else border-gray-300 @endif pb-2">
                    <div class="flex justify-between items-start mb-1">
                        @php
                            $badgeColor = match ($activity['color']) {
                                'success' => 'success',
                                'primary' => 'primary',
                                'info' => 'info',
                                'warning' => 'warning',
                                'danger' => 'danger',
                                default => 'gray',
                            };
                        @endphp
                        <x-filament::badge :color="$badgeColor" size="sm" class="mb-1">
                            {{ $activity['type'] }}
                        </x-filament::badge>
                        <span class="text-[10px] uppercase font-bold text-gray-400">
                            {{ $activity['date']->diffForHumans() }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">
                            {{ $activity['title'] }}
                        </h3>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">
                        {{ $activity['desc'] }}
                    </p>

                    <div class="flex items-center gap-1 mt-2 text-gray-400">
                        <x-filament::icon icon="heroicon-m-user" class="h-3 w-3" />
                        <span class="text-[10px] font-medium">{{ $activity['user'] }}</span>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                    No recent activity recorded.
                </div>
            @endforelse
        </div>

        <div class="mt-4 text-center">
            <x-filament::button tag="a" href="{{ \App\Filament\Pages\ActivityFeed::getUrl() }}" color="gray" size="sm"
                outlined class="px-10">
                View All Activity
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>