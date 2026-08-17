<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class DashboardActionCardsWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-action-cards';

    protected static ?int $sort = -8;
    protected int | string | array $columnSpan = 'full';

    public ?int $year = null;

    public function mount(): void
    {
        $this->year = (int) \Illuminate\Support\Facades\Session::get('global_dashboard_year', now()->year);
    }

    #[On('dashboard-year-changed')]
    public function onYearChanged(int $year): void
    {
        $this->year = $year;
    }

    public static function canView(): bool
    {
        return !auth()->user()?->isSuperAdmin();
    }

    protected function getViewData(): array
    {
        return [
            'year' => $this->year ?? (int) now()->year,
        ];
    }
}
