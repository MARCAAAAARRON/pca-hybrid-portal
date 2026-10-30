<?php

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

trait HasApprovalWorkflow
{
    // ───── Relationships ─────

    public function preparedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by')->withTrashed();
    }

    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }

    public function notedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'noted_by')->withTrashed();
    }

    // ───── Status Helpers ─────

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPrepared(): bool
    {
        return $this->status === 'prepared';
    }

    public function isReviewed(): bool
    {
        return $this->status === 'reviewed';
    }

    public function isNoted(): bool
    {
        return $this->status === 'noted';
    }

    // ───── Workflow Actions ─────

    /**
     * Mark as Prepared. Records the authenticated acting user.
     */
    public function markAsPrepared(User $actingUser): string
    {
        $this->status = 'prepared';
        $this->prepared_by = $actingUser->id;
        $this->date_prepared = now();
        $this->save();

        $tableTitle = class_basename($this);
        $siteName = $this->fieldSite ? $this->fieldSite->name : 'Global';
        $this->notifyUsers(['manager'], "Record Prepared", "A {$tableTitle} record for {$siteName} is waiting for your review.", $actingUser);

        return "Record prepared by {$actingUser->name}.";
    }

    /**
     * Mark as Reviewed. Implements:
     * - Maker-checker: cannot review if you prepared it.
     */
    public function markAsReviewed(User $actingUser): string|false
    {
        // Trapping: Cannot review own prepared record
        if ($this->prepared_by === $actingUser->id) {
            return false;
        }

        $this->status = 'reviewed';
        $this->reviewed_by = $actingUser->id;
        $this->date_reviewed = now();
        $this->save();

        $tableTitle = class_basename($this);
        $siteName = $this->fieldSite ? $this->fieldSite->name : 'Global';
        $this->notifyUsers(['admin', 'superadmin'], "Record Reviewed", "A {$tableTitle} record for {$siteName} has been reviewed and requires noting.", $actingUser);

        return "Record reviewed by {$actingUser->name}.";
    }

    /**
     * Mark as Noted. Implements:
     * - Maker-checker: cannot note if you prepared or reviewed it.
     */
    public function markAsNoted(User $actingUser): string|false
    {
        // Trapping: Cannot note if you prepared or reviewed
        if (in_array($actingUser->id, [$this->prepared_by, $this->reviewed_by])) {
            return false;
        }

        $this->status = 'noted';
        $this->noted_by = $actingUser->id;
        $this->date_noted = now();
        $this->save();

        $tableTitle = class_basename($this);
        $siteName = $this->fieldSite ? $this->fieldSite->name : 'Global';
        $this->notifyUsers(['supervisor'], "Record Noted", "Your {$tableTitle} record for {$siteName} has been officially noted.", $actingUser);

        return 'Record officially noted.';
    }

    /**
     * Return to Draft. Resets all signatories and notifies relevant personnel.
     */
    public function returnToDraft(User $actingUser, ?string $remarks = null): string
    {
        $this->status = 'draft';
        $this->prepared_by = null;
        $this->date_prepared = null;
        $this->reviewed_by = null;
        $this->date_reviewed = null;
        $this->noted_by = null;
        $this->date_noted = null;
        if (in_array('return_remarks', $this->getFillable()) || Schema::hasColumn($this->getTable(), 'return_remarks')) {
            $this->return_remarks = $remarks;
        }
        $this->save();

        $tableTitle = class_basename($this);
        $siteName = $this->fieldSite ? $this->fieldSite->name : 'Global';
        $this->notifyUsers(['supervisor', 'manager'], "Record Returned to Draft", "A {$tableTitle} record for {$siteName} has been returned to draft by {$actingUser->name}.", $actingUser);

        return 'Record returned to draft and personnel notified.';
    }
    protected function getReportUrl(): string
    {
        $category = match(class_basename($this)) {
            'MonthlyHarvest' => 'monthly_harvest',
            'PollenProduction' => 'pollen_production',
            'NurseryOperation' => $this->report_type === 'terminal' ? 'terminal_report' : 'nursery_operation',
            'HybridDistribution' => 'hybrid_distribution',
            'HybridizationRecord' => 'hybridization_record',
            default => '',
        };

        $year = null;
        $month = null;
        if (isset($this->report_month)) {
            $parsed = \Carbon\Carbon::parse($this->report_month);
            $year = $parsed->year;
            $month = $parsed->month;
        }

        return \App\Filament\Pages\ReportsDashboard::getUrl() . '?' . http_build_query(array_filter([
            'category' => $category,
            'year' => $year,
            'month' => $month,
            'field_site_id' => $this->field_site_id,
        ]));
    }

    protected function notifyUsers($roleOrRoles, $title, $body, $actingUser = null)
    {
        $roles = (array) $roleOrRoles;
        $siteId = $this->field_site_id;

        $users = User::whereIn('role', $roles)->get()->filter(function ($user) use ($siteId) {
            if (in_array($user->role, ['admin', 'superadmin'])) return true;
            if ($siteId && $user->field_site_id && $user->field_site_id != $siteId) return false;
            return true;
        });

        // For demo purposes: Guarantee the acting user ALWAYS receives a copy of the notification they triggered
        if ($actingUser && !$users->contains('id', $actingUser->id)) {
            $users->push($actingUser);
        }

        \Illuminate\Support\Facades\Log::info("notifyUsers executing for roles: " . implode(',', $roles) . " | Found users: " . $users->count());

        foreach ($users as $user) {
            \Illuminate\Support\Facades\Log::info("Sending notification to user ID: " . $user->id);
            $notification = \Filament\Notifications\Notification::make()
                ->title($title)
                ->body($body)
                ->info()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view_report')
                        ->label('View in Reports Dashboard')
                        ->button()
                        ->url($this->getReportUrl())
                        ->markAsRead(),
                ]);
            
            // Convert 'warning' to actual warning if it's draft
            if ($title === "Record Returned to Draft") {
                $notification->warning();
            } else if ($title === "Record Noted") {
                $notification->success();
            }

            try {
                $notification->sendToDatabase($user);
                \Illuminate\Support\Facades\Log::info("Successfully sent to user ID: " . $user->id);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send notification to user ID: " . $user->id . " | Error: " . $e->getMessage());
            }
        }
    }
}
