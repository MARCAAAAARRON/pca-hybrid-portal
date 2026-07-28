<?php

namespace App\Filament\Widgets;

use App\Models\HybridizationRecord;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingValidations extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Pending Validations';

    public static function canView(): bool
    {
        if (auth()->user()?->role === \'sub_supervisor\') return false;
        return false; // Removed from dashboard per admin request
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HybridizationRecord::where('status', 'submitted')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('hybrid_code')
                    ->label('HYBRID CODE')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('crop_type')
                    ->label('CROP TYPE')
                    ->badge(),
                Tables\Columns\TextColumn::make('fieldSite.name')
                    ->label('FIELD SITE')
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('SUBMITTED BY')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('DATE')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('validate')
                        ->label('Validate')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (HybridizationRecord $record) => $record->update(['status' => 'validated'])),
                ]),
            ])
            ->paginated(false)
            ->headerActions([
                Tables\Actions\Action::make('all_records')
                    ->label('All Records')
                    ->url(\App\Filament\Resources\HybridizationRecordResource::getUrl('index'))
                    ->button()
                    ->outlined(),
            ]);
    }
}
