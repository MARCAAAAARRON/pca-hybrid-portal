<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class GlobalYearFilter extends Component
{
    public ?int $year = null;

    public function mount(): void
    {
        // Check session first, if not exist then current year
        $this->year = (int) Session::get('global_dashboard_year', now()->year);
    }

    public function updatedYear($value): void
    {
        $this->year = (int) $value;
        // Persist so when reloading it remembers
        Session::put('global_dashboard_year', $this->year);
        // Dispatch to filament widgets
        $this->dispatch('dashboard-year-changed', year: $this->year);
    }

    public function render()
    {
        return view('livewire.global-year-filter', [
            'yearOptions' => collect(range(now()->year, 2024, -1))
                ->mapWithKeys(fn ($y) => [$y => $y])
                ->toArray(),
        ]);
    }
}
