<div class="flex items-center me-2 ms-1">
    @if($weather)
        <div
            x-data="{ open: false }"
            x-on:click.away="open = false"
            class="relative"
        >
            {{-- Compact green trigger button matching global-year-filter --}}
            <button
                x-on:click="open = !open"
                type="button"
                class="flex items-center h-8 rounded-full px-3 py-1 shadow-sm transition-all cursor-pointer group"
                style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35);"
                title="Weather in {{ $city }}"
            >
                @if(isset($weather['weather'][0]['icon']))
                    <img
                        src="https://openweathermap.org/img/wn/{{ $weather['weather'][0]['icon'] }}@2x.png"
                        alt="Weather"
                        class="w-5 h-5 -my-1 me-1.5 shrink-0 object-contain drop-shadow-sm"
                    >
                @else
                    <x-heroicon-o-cloud class="w-4 h-4 text-emerald-600 dark:text-emerald-400 me-1.5 shrink-0" />
                @endif
                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 tracking-tight me-1.5">
                    {{ round($weather['main']['temp']) }}°C
                </span>
                <x-heroicon-m-chevron-down
                    class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform shrink-0"
                    x-bind:class="open ? 'rotate-180' : ''"
                />
            </button>

            {{-- Expanded dropdown panel --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                style="position: absolute; right: 0; top: 100%; margin-top: 8px; z-index: 50; width: 320px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2); border: 1px solid rgba(229, 231, 235, 0.2); background-color: #111827; overflow: hidden;"
                x-cloak
            >
                {{-- Green header section --}}
                <div style="position: relative; padding: 16px; color: #ffffff; background: linear-gradient(135deg, #10b981, #0b9e4f); display: flex; align-items: center; gap: 16px;">
                    {{-- Background pattern --}}
                    <div style="position: absolute; inset: 0; background-image: radial-gradient(white 1px, transparent 1px); background-size: 16px 16px; opacity: 0.25; pointer-events: none;"></div>

                    {{-- Left Icon Container --}}
                    <div style="width: 56px; height: 56px; min-width: 56px; max-width: 56px; min-height: 56px; max-height: 56px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(4px); overflow: hidden;">
                        @if(isset($weather['weather'][0]['icon']))
                            <img 
                                src="https://openweathermap.org/img/wn/{{ $weather['weather'][0]['icon'] }}@4x.png" 
                                alt="Weather" 
                                style="width: 44px; height: 44px; max-width: 44px; max-height: 44px; object-fit: contain; display: block;"
                            >
                        @else
                            <x-heroicon-o-cloud style="width: 28px; height: 28px; color: #ffffff;" />
                        @endif
                    </div>

                    {{-- Right Info Container --}}
                    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center;">
                        <div style="display: flex; align-items: baseline; gap: 2px;">
                            <span style="font-size: 2.25rem; font-weight: 800; line-height: 1; letter-spacing: -0.03em;">
                                {{ round($weather['main']['temp']) }}°
                            </span>
                            <span style="font-size: 1.125rem; font-weight: 700; color: #d1fae5;">C</span>
                        </div>
                        <div style="font-size: 0.9375rem; font-weight: 600; text-transform: capitalize; color: #ecfdf5; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">
                            {{ $weather['weather'][0]['description'] ?? 'Unknown' }}
                        </div>
                        <div style="display: flex; align-items: center; gap: 4px; font-size: 0.75rem; color: #d1fae5; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <x-heroicon-m-map-pin style="width: 14px; height: 14px; flex-shrink: 0;" />
                            <span style="font-weight: 500;">{{ $city }}</span>
                        </div>
                    </div>
                </div>

                {{-- Bottom Stats Row --}}
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 8px; background-color: #111827; border-top: 1px solid rgba(255,255,255,0.05);">
                    {{-- Humidity --}}
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; text-align: center; padding: 0 4px;">
                        <x-heroicon-o-cloud style="width: 20px; height: 20px; color: #34d399; margin-bottom: 4px;" />
                        <span style="font-size: 0.875rem; font-weight: 700; color: #ffffff; line-height: 1;">
                            {{ $weather['main']['humidity'] }}%
                        </span>
                        <span style="font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin-top: 4px;">
                            Humidity
                        </span>
                    </div>

                    {{-- Divider --}}
                    <div style="width: 1px; height: 32px; background-color: rgba(156, 163, 175, 0.2); flex-shrink: 0;"></div>

                    {{-- Wind --}}
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; text-align: center; padding: 0 4px;">
                        <x-heroicon-o-paper-airplane style="width: 20px; height: 20px; color: #34d399; margin-bottom: 4px;" />
                        <span style="font-size: 0.875rem; font-weight: 700; color: #ffffff; line-height: 1;">
                            {{ round($weather['wind']['speed']) }} <span style="font-size: 0.625rem; font-weight: 500; color: #9ca3af;">m/s</span>
                        </span>
                        <span style="font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin-top: 4px;">
                            Wind
                        </span>
                    </div>

                    {{-- Divider --}}
                    <div style="width: 1px; height: 32px; background-color: rgba(156, 163, 175, 0.2); flex-shrink: 0;"></div>

                    {{-- Pressure --}}
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; text-align: center; padding: 0 4px;">
                        <x-heroicon-o-arrows-up-down style="width: 20px; height: 20px; color: #34d399; margin-bottom: 4px;" />
                        <span style="font-size: 0.875rem; font-weight: 700; color: #ffffff; line-height: 1;">
                            {{ $weather['main']['pressure'] }} <span style="font-size: 0.625rem; font-weight: 500; color: #9ca3af;">hPa</span>
                        </span>
                        <span style="font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin-top: 4px;">
                            Pressure
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
