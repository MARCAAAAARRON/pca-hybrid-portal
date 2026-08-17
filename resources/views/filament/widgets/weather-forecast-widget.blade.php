<x-filament-widgets::widget>
    <div class="overflow-hidden rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 transition-all hover:shadow-md flex flex-col sm:flex-row">
        @if($weather)
            <!-- Left: Icon, Temp, Location (Green Section) -->
            <div class="relative w-full sm:w-5/12 p-6 sm:p-8 flex flex-col justify-center text-white" style="background: linear-gradient(135deg, #10b981, #0b9e4f);">
                <!-- subtle background pattern overlay -->
                <div class="absolute inset-0 bg-white/5 opacity-50 pointer-events-none" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 20px 20px;"></div>
                
                <div class="relative flex items-center gap-4 sm:gap-6">
                    <div class="relative w-20 h-20 sm:w-24 sm:h-24 shrink-0 flex items-center justify-center rounded-full bg-white/20 backdrop-blur-sm shadow-inner transform transition-transform hover:scale-105 duration-300">
                        @if(isset($weather['weather'][0]['icon']))
                            <img src="https://openweathermap.org/img/wn/{{ $weather['weather'][0]['icon'] }}@4x.png" alt="Weather icon" class="w-full h-full object-contain filter drop-shadow-md">
                        @else
                            <x-filament::icon icon="heroicon-o-cloud" class="w-10 h-10 text-white" />
                        @endif
                    </div>
                    
                    <div>
                        <div class="flex items-baseline gap-1">
                            <h3 class="text-4xl sm:text-5xl font-black text-white tracking-tight">
                                {{ round($weather['main']['temp']) }}&deg;
                            </h3>
                            <span class="text-xl font-bold" style="color: #d1fae5;">C</span>
                        </div>
                        <p class="text-lg font-medium capitalize mt-1" style="color: #ecfdf5;">
                            {{ $weather['weather'][0]['description'] ?? 'Unknown' }}
                        </p>
                        <div class="flex items-center gap-1.5 mt-1 text-sm" style="color: #d1fae5;">
                            <x-filament::icon icon="heroicon-m-map-pin" class="w-4 h-4" />
                            <span class="font-medium tracking-wide">{{ $city }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right: Stats (White Section) -->
            <div class="w-full sm:w-7/12 p-6 sm:p-8 flex items-center justify-evenly bg-white dark:bg-gray-900">
                <div class="flex flex-col items-center justify-center flex-1">
                    <x-filament::icon icon="heroicon-o-cloud" class="w-7 h-7 text-gray-400 dark:text-gray-500 mb-2" />
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $weather['main']['humidity'] }}%</span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mt-1">Humidity</span>
                </div>
                
                <div class="w-px h-16 bg-gray-200 dark:bg-gray-700"></div>
                
                <div class="flex flex-col items-center justify-center flex-1">
                    <x-filament::icon icon="heroicon-o-paper-airplane" class="w-7 h-7 text-gray-400 dark:text-gray-500 mb-2" />
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ round($weather['wind']['speed']) }} <span class="text-sm font-medium">m/s</span></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mt-1">Wind</span>
                </div>
                
                <div class="w-px h-16 bg-gray-200 dark:bg-gray-700"></div>
                
                <div class="flex flex-col items-center justify-center flex-1">
                    <x-filament::icon icon="heroicon-o-arrows-up-down" class="w-7 h-7 text-gray-400 dark:text-gray-500 mb-2" />
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $weather['main']['pressure'] }} <span class="text-sm font-medium">hPa</span></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mt-1">Pressure</span>
                </div>
            </div>
        @else
            <div class="w-full p-6 flex flex-col items-center justify-center text-center gap-3 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 min-h-[160px]">
                <x-filament::icon icon="heroicon-o-cloud-slash" class="w-10 h-10 text-gray-400 dark:text-gray-500 mb-2" />
                <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Weather Unavailable</h4>
                <p class="text-sm max-w-sm">We couldn't load the weather data for your location. Please check your API configuration or internet connection.</p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
