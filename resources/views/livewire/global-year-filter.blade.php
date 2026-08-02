<div class="flex items-center me-4">
    <div class="relative flex items-center bg-gray-100 dark:bg-gray-800 rounded-full px-3 py-1 shadow-sm transition-colors border border-gray-200 dark:border-gray-700" title="Filter dashboard data by year">
        <x-heroicon-m-calendar-days class="w-4 h-4 text-gray-500 dark:text-gray-400 mr-2" />
        <select 
            wire:model.live="year"
            class="bg-transparent border-none text-sm font-semibold text-gray-700 dark:text-gray-200 focus:ring-0 py-0 pl-0 pr-6 appearance-none cursor-pointer"
        >
            @foreach($yearOptions as $val => $label)
                <option value="{{ $val }}" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">{{ $label }}</option>
            @endforeach
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
            <x-heroicon-m-chevron-down class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        </div>
    </div>
</div>
