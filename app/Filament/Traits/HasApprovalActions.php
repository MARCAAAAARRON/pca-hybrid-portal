<?php

namespace App\Filament\Traits;

use Filament\Notifications\Notification;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

/**
 * Reusable approval workflow Table Actions for Filament Resources.
 * Add this trait to any Filament Resource that uses a model with HasApprovalWorkflow.
 */
trait HasApprovalActions
{
    /**
     * Get the status badge column for tables.
     */
    public static function getStatusColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('status')
            ->badge()
            ->color(fn (string $state): string => match ($state) {
                'draft' => 'gray',
                'prepared' => 'info',
                'reviewed' => 'warning',
                'noted' => 'success',
                default => 'gray',
            })
            ->formatStateUsing(fn (string $state) => ucfirst($state))
            ->sortable();
    }

    /**
     * Get the approval workflow table actions.
     */
    public static function getApprovalActions(): array
    {
        $user = auth()->user();

        return [
            // ── Prepare Action ──
            Tables\Actions\Action::make('prepare')
                ->label('Prepare')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Mark as Prepared?')
                ->modalDescription('This will submit the record for review.')
                ->visible(fn (Model $record) => 
                    $record->isDraft() &&
                    auth()->user()->role === 'supervisor'
                )
                ->action(function (Model $record) {
                    $msg = $record->markAsPrepared(auth()->user());
                    Notification::make()->success()->title($msg)->send();
                }),

            // ── Review Action ──
            Tables\Actions\Action::make('review')
                ->label('Review')
                ->icon('heroicon-o-check-badge')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Mark as Reviewed?')
                ->modalDescription('This will confirm the record has been reviewed.')
                ->visible(fn (Model $record) => 
                    $record->isPrepared() &&
                    auth()->user()->role === 'manager'
                )
                ->action(function (Model $record) {
                    $result = $record->markAsReviewed(auth()->user());
                    if ($result === false) {
                        Notification::make()
                            ->danger()
                            ->title('Trapping: You cannot review a record you prepared.')
                            ->send();
                        return;
                    }
                    Notification::make()->success()->title($result)->send();
                }),

            // ── Note Action ──
            Tables\Actions\Action::make('note')
                ->label('Note')
                ->icon('heroicon-o-document-check')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mark as Noted?')
                ->modalDescription('This will officially note the record.')
                ->visible(fn (Model $record) => 
                    $record->isReviewed() &&
                    in_array(auth()->user()->role, ['admin', 'superadmin'])
                )
                ->action(function (Model $record) {
                    $result = $record->markAsNoted(auth()->user());
                    if ($result === false) {
                        Notification::make()
                            ->danger()
                            ->title('Trapping: You cannot note a record you previously signed.')
                            ->send();
                        return;
                    }
                    Notification::make()->success()->title($result)->send();
                }),

            // ── Return to Draft Action ──
            Tables\Actions\Action::make('returnToDraft')
                ->label('Return to Draft')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Return to Draft?')
                ->modalDescription('This will reset all signatories and return the record to draft status.')
                ->visible(function (Model $record) {
                    if ($record->isDraft()) return false;
                    
                    $role = auth()->user()->role;
                    if (in_array($role, ['admin', 'superadmin'])) return true;
                    if ($role === 'manager' && !$record->isNoted()) return true;
                    
                    return false;
                })
                ->action(function (Model $record) {
                    $msg = $record->returnToDraft(auth()->user());
                    Notification::make()->success()->title($msg)->send();
                }),
        ];
    }

    /**
     * Get a status filter for tables.
     */
    public static function getStatusFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('status')
            ->options([
                'draft' => 'Draft',
                'prepared' => 'Prepared',
                'reviewed' => 'Reviewed',
                'noted' => 'Noted',
            ]);
    }
}
