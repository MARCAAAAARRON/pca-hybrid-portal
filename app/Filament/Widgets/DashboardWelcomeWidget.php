<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class DashboardWelcomeWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-welcome';

    protected static ?int $sort = -10;
    protected int | string | array $columnSpan = 'full';

    public function mount(): void
    {
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        return [
            'user' => $user,
        ];
    }
}
