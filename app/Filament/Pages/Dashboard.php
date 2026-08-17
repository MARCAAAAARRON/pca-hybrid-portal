<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Home';
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getColumns(): int | string | array
    {
        return 1;
    }

    /**
     * Explicitly list only operational widgets.
     * Farm/production overview widgets are now on the dedicated FarmOverview page.
     */
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardWelcomeWidget::class,
            \App\Filament\Widgets\SysAdminStats::class,
            \App\Filament\Widgets\SysAdminQuickActions::class,
            \App\Filament\Widgets\DashboardServicesWidget::class,
            \App\Filament\Widgets\DashboardActionCardsWidget::class,
            \App\Filament\Widgets\RecentActivityWidget::class,
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Home';
    }

    public function getSubheading(): string|Htmlable|null
    {
        // Removed subheading to favor our DashboardWelcomeWidget
        return null;
    }

    protected function getHeaderActions(): array
    {
        // Removed header actions since we now feature highly visible "Quick Links" cards in the UI
        return [];
    }
}
