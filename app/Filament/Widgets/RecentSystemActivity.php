<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentSystemActivity extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Recent Portal Activity';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AuditLog::query()
                    ->with('user')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('TIMESTAMP')
                    ->dateTime('M j, H:i')
                    ->description(fn (AuditLog $record): string => $record->created_at->diffForHumans()),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('USER')
                    ->weight('bold')
                    ->description(fn (AuditLog $record): string => $record->user?->role_title ?? 'User'),
                Tables\Columns\TextColumn::make('action')
                    ->label('ACTION')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'create', 'login' => 'success',
                        'update', 'validate' => 'info',
                        'delete', 'revision' => 'danger',
                        'report', 'submit' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => AuditLog::ACTION_CHOICES[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('model_name')
                    ->label('MODULE')
                    ->formatStateUsing(fn (string $state): string => str_replace('App\\Models\\', '', $state))
                    ->color('gray'),
                Tables\Columns\TextColumn::make('formatted_details')
                    ->label('DETAILS')
                    ->limit(50)
                    ->tooltip(fn (AuditLog $record): string => $record->formatted_details),
            ])
            ->paginated(false)
            ->headerActions([
                Tables\Actions\Action::make('view_full_log')
                    ->label('View Audit Trail')
                    ->url(\App\Filament\Resources\AuditLogResource::getUrl('index'))
                    ->button()
                    ->outlined(),
            ]);
    }
}
