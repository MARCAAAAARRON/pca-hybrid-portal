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
        if (auth()->user()?->role === 'sub_supervisor') return false;
        return ! auth()->user()?->isSuperAdmin();
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
                // Resource has no 'view' page, link to 'edit' instead
                'url' => \App\Filament\Resources\EventDocumentationResource::getUrl('edit', ['record' => $event]),
                'backgroundColor' => '#3b82f6', // blue
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
                'url' => \App\Filament\Resources\HybridizationRecordResource::getUrl('view', ['record' => $record]),
                'backgroundColor' => '#a855f7', // purple
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
                'url' => \App\Filament\Resources\PollenProductionResource::getUrl('view', ['record' => $record]),
                'backgroundColor' => '#eab308', // yellow
            ];
        }
        
        // 4. Monthly Harvests — uses 'report_month' (not 'harvest_date')
        $harvests = MonthlyHarvest::query()
            ->whereBetween('report_month', [$fetchInfo['start'], $fetchInfo['end']])
            ->get();
            
        foreach ($harvests as $harvest) {
            $events[] = [
                'id' => 'harvest_' . $harvest->id,
                'title' => 'Harvest: ' . ($harvest->fieldSite->name ?? 'Site'),
                'start' => $harvest->report_month->toDateString(),
                'url' => \App\Filament\Resources\MonthlyHarvestResource::getUrl('view', ['record' => $harvest]),
                'backgroundColor' => '#22c55e', // green
            ];
        }

        // 5. Nursery Operations — uses 'nursery_start_date' (not 'date_sown' which is on batches, not operations)
        $nurseryOps = NurseryOperation::query()
            ->whereNotNull('nursery_start_date')
            ->whereBetween('nursery_start_date', [$fetchInfo['start'], $fetchInfo['end']])
            ->get();
            
        foreach ($nurseryOps as $op) {
            $events[] = [
                'id' => 'nursery_' . $op->id,
                'title' => 'Nursery: ' . ($op->proponent_entity ?? 'Operation'),
                'start' => $op->nursery_start_date->toDateString(),
                'url' => \App\Filament\Resources\NurseryOperationResource::getUrl('view', ['record' => $op]),
                'backgroundColor' => '#a16207', // brown
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
            ];
        }

        return $events;
    }
}
