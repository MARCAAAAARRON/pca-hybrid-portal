<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardServicesWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-services';

    protected static ?int $sort = -8;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        if (auth()->user()?->isSubSupervisor()) return false;
        return !auth()->user()?->isSuperAdmin();
    }
}
