<div class="flex items-center me-2">
    <div 
        class="relative flex items-center h-8 rounded-full px-3 py-1 shadow-sm transition-all cursor-pointer group" 
        style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35);"
        title="Filter dashboard data by year"
    >
        <x-heroicon-m-calendar-days class="w-4 h-4 text-emerald-600 dark:text-emerald-400 me-1.5 shrink-0" />
        <select 
            wire:model.live="year"
            class="bg-transparent border-none text-xs font-bold text-emerald-800 dark:text-emerald-300 focus:ring-0 py-0 pl-0 pr-5 appearance-none cursor-pointer tracking-tight"
        >
            @foreach($yearOptions as $val => $label)
                <option value="{{ $val }}" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">{{ $label }}</option>
            @endforeach
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
            <x-heroicon-m-chevron-down class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform" />
        </div>
    </div>
</div>
