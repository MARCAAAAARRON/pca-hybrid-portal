<?php

namespace App\Filament\Resources\PollenProductionResource\Widgets;

use App\Models\PollenProduction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PollenStockWidget extends BaseWidget
{
    protected static ?int $sort = 10;
    protected int | string | array $columnSpan = 'half';
    protected static ?string $heading = '🧪 Pollen Stock & Viability';

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(function () use ($user) {
                $query = PollenProduction::query()
                    ->with('fieldSite')
                    ->where('ending_balance', '>', 0);

                // Supervisors only see their site
                if ($user?->isSupervisor() && $user->field_site_id) {
                    $query->where('field_site_id', $user->field_site_id);
                }

                return $query->orderByRaw("COALESCE(date_received, report_month) ASC"); // Oldest first
            })
            ->columns([
                Tables\Columns\TextColumn::make('fieldSite.name')
                    ->label('Site')
                    ->limit(15)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) return null;
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('pollen_variety')
                    ->label('Variety')
                    ->weight('bold')
                    ->searchable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('ending_balance')
                    ->label('Balance (g)')
                    ->numeric(2)
                    ->color('primary')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('viability_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fresh' => 'success',
                        'at_risk' => 'warning',
                        'expired' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fresh' => 'Fresh',
                        'at_risk' => 'At Risk',
                        'expired' => 'Expired',
                        default => 'Unknown',
                    }),
                Tables\Columns\TextColumn::make('pollen_age_days')
                    ->label('Age')
                    ->suffix(' days')
                    ->color(fn ($state) => $state > 60 ? 'danger' : ($state > 30 ? 'warning' : 'success')),
            ])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No Active Stock')
            ->emptyStateDescription('All recorded pollen has been fully utilized.')
            ->headerActions([
                Tables\Actions\Action::make('all_pollen')
                    ->label('All Inventory')
                    ->url(\App\Filament\Resources\PollenProductionResource::getUrl('index'))
                    ->button()
                    ->outlined()
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->size('sm'),
            ]);
    }
}
