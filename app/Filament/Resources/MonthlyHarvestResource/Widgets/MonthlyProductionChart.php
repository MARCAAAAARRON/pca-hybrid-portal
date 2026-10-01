<?php

namespace App\Filament\Resources\MonthlyHarvestResource\Widgets;

use App\Models\MonthlyHarvest;
use Filament\Widgets\ChartWidget;

class MonthlyProductionChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Seednut Production';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $user = auth()->user();
        $year = now()->year;

        $query = MonthlyHarvest::query()->whereYear('report_month', $year);

        if (($user?->isSupervisor() || $user?->isSubSupervisor()) && $user->field_site_id) {
            $query->where('field_site_id', $user->field_site_id);
        }

        $harvests = $query->get();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $productionFields = [
            'production_jan', 'production_feb', 'production_mar',
            'production_apr', 'production_may', 'production_jun',
            'production_jul', 'production_aug', 'production_sep',
            'production_oct', 'production_nov', 'production_dec',
        ];

        $data = [];
        foreach ($productionFields as $field) {
            $data[] = $harvests->sum($field);
        }

        return [
            'datasets' => [
                [
                    'label' => "Seednut Production {$year}",
                    'data' => $data,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
