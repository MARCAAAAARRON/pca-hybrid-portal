<?php

namespace App\Filament\Widgets;

use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Models\EventDocumentation;
use App\Models\HybridizationRecord;
use App\Models\PollenProduction;
use App\Models\MonthlyHarvest;
use App\Models\NurseryOperation;

class OrganizationCalendarWidget extends FullCalendarWidget
{
    public static function canView(): bool
    {
        // Visible for all roles except superadmin (who has a separate admin dashboard)
        return ! auth()->user()?->isSuperAdmin();
    }

    /**
     * Disable the built-in header Create button.
     * Event creation is handled by the page-level "Create Reminder" header action.
     */
    protected function headerActions(): array
    {
        return [];
    }

    public function viewAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('view')
            ->modalHeading(fn (array $arguments) => $arguments['event']['title'] ?? 'Event Details')
            ->modalContent(fn (array $arguments) => view('filament.widgets.calendar-event-modal', ['event' => $arguments['event'] ?? []]))
            ->modalSubmitAction(false)
            ->modalCancelAction(fn ($action) => $action->label('Close'));
    }

    /**
     * Disable date-select create modal — creation is via the "Create Reminder" page action.
     */
    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {
        // intentionally no-op: prevents 419/500 from the missing create action
    }


    public function fetchEvents(array $fetchInfo): array
    {
        $events = [];

        // 1. PCA Events
        $pcaEvents = EventDocumentation::query()
            ->whereBetween('event_date', [$fetchInfo['start'], $fetchInfo['end']])
            ->get();
            
        foreach ($pcaEvents as $event) {
            $events[] = [
                'id' => 'event_' . $event->id,
                'title' => 'Event: ' . $event->title,
                'start' => $event->event_date->toDateString(),
                'record_url' => \App\Filament\Resources\EventDocumentationResource::getUrl('edit', ['record' => $event]),
                'backgroundColor' => '#3b82f6', // blue
                'details' => [
                    'Date' => $event->event_date->format('F d, Y'),
                    'Location' => $event->location ?? 'N/A',
                    'Description' => $event->description ?? 'No description provided.',
                ]
            ];
        }

        // 2. Hybridization Records
        $hybridRecords = HybridizationRecord::query()
            ->whereBetween('date_planted', [$fetchInfo['start'], $fetchInfo['end']])
            ->get();
            
        foreach ($hybridRecords as $record) {
            $events[] = [
                'id' => 'hybrid_' . $record->id,
                'title' => 'Hybrid Planted: ' . ($record->hybrid_code ?? 'Draft'),
                'start' => $record->date_planted->toDateString(),
                'record_url' => \App\Filament\Resources\HybridizationRecordResource::getUrl('edit', ['record' => $record]),
                'backgroundColor' => '#a855f7', // purple
                'details' => [
                    'Date Planted' => $record->date_planted->format('F d, Y'),
                    'Hybrid Code' => $record->hybrid_code ?? 'N/A',
                    'Cross Combination' => $record->cross_combination ?? 'N/A',
                ]
            ];
        }

        // 3. Pollen Production
        $pollenRecords = PollenProduction::query()
            ->whereBetween('report_month', [$fetchInfo['start'], $fetchInfo['end']])
            ->get();
            
        foreach ($pollenRecords as $record) {
            $events[] = [
                'id' => 'pollen_' . $record->id,
                'title' => 'Pollen Received: ' . ($record->pollen_variety ?? 'Unknown'),
                'start' => $record->report_month->toDateString(),
                'record_url' => \App\Filament\Resources\PollenProductionResource::getUrl('edit', ['record' => $record]),
                'backgroundColor' => '#eab308', // yellow
                'details' => [
                    'Report Month' => $record->report_month->format('F Y'),
                    'Variety' => $record->pollen_variety ?? 'N/A',
                    'Quantity (g)' => $record->quantity_grams ?? '0',
                    'Proponent' => $record->proponent_entity ?? 'N/A',
                ]
            ];
        }
        
        // 4. Monthly Harvests
        $harvests = MonthlyHarvest::query()
            ->whereBetween('report_month', [$fetchInfo['start'], $fetchInfo['end']])
            ->get();
            
        foreach ($harvests as $harvest) {
            $events[] = [
                'id' => 'harvest_' . $harvest->id,
                'title' => 'Harvest: ' . ($harvest->fieldSite->name ?? 'Site'),
                'start' => $harvest->report_month->toDateString(),
                'record_url' => \App\Filament\Resources\MonthlyHarvestResource::getUrl('edit', ['record' => $harvest]),
                'backgroundColor' => '#22c55e', // green
                'details' => [
                    'Report Month' => $harvest->report_month->format('F Y'),
                    'Field Site' => $harvest->fieldSite->name ?? 'N/A',
                    'Total Seednuts' => $harvest->total_seednuts ?? '0',
                    'Total Bunches' => $harvest->total_bunches ?? '0',
                ]
            ];
        }

        // 5. Nursery Operations
        $nurseryOps = NurseryOperation::query()
            ->whereNotNull('nursery_start_date')
            ->whereBetween('nursery_start_date', [$fetchInfo['start'], $fetchInfo['end']])
            ->get();
            
        foreach ($nurseryOps as $op) {
            $events[] = [
                'id' => 'nursery_' . $op->id,
                'title' => 'Nursery: ' . ($op->proponent_entity ?? 'Operation'),
                'start' => $op->nursery_start_date->toDateString(),
                'record_url' => \App\Filament\Resources\NurseryOperationResource::getUrl('edit', ['record' => $op]),
                'backgroundColor' => '#a16207', // brown
                'details' => [
                    'Start Date' => $op->nursery_start_date->format('F d, Y'),
                    'Proponent' => $op->proponent_entity ?? 'N/A',
                    'Seednuts Sown' => $op->seednuts_sown ?? '0',
                ]
            ];
        }

        // 6. Calendar Reminders
        $reminders = \App\Models\CalendarReminder::query()
            ->whereBetween('reminder_date', [$fetchInfo['start'], $fetchInfo['end']])
            ->where(function ($query) {
                $query->where('type', 'organizational')
                      ->orWhere(function ($q) {
                          $q->where('type', 'personal')->where('user_id', auth()->id());
                      });
            })
            ->get();
            
        foreach ($reminders as $reminder) {
            $events[] = [
                'id' => 'reminder_' . $reminder->id,
                'title' => ($reminder->type === 'organizational' ? 'Org Reminder: ' : 'Personal: ') . $reminder->title,
                'start' => $reminder->reminder_date->toDateString(),
                'backgroundColor' => $reminder->type === 'organizational' ? '#ef4444' : '#64748b', // red vs slate
                'details' => [
                    'Type' => ucfirst($reminder->type),
                    'Date' => \Carbon\Carbon::parse($reminder->reminder_date)->format('F d, Y'),
                    'Description' => $reminder->description ?? 'No extra details.',
                ]
            ];
        }

        return $events;
    }
}
