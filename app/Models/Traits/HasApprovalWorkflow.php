<?php

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasApprovalWorkflow
{
    // ───── Relationships ─────

    public function preparedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function notedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'noted_by');
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
     * Mark as Prepared. Implements signatory attribution:
     * If the acting user is NOT a supervisor, attribute to the site's supervisor.
     */
    public function markAsPrepared(User $actingUser): string
    {
        $sigUser = $actingUser;

        // Attribution: If not a supervisor, try to find the site's supervisor
        if ($actingUser->role !== 'supervisor' && $this->field_site_id) {
            $supervisor = User::where('field_site_id', $this->field_site_id)
                ->where('role', 'supervisor')
                ->first();
            if ($supervisor) {
                $sigUser = $supervisor;
            }
        }

        $this->status = 'prepared';
        $this->prepared_by = $sigUser->id;
        $this->date_prepared = now();
        $this->save();

        $tableTitle = class_basename($this);
        $siteName = $this->fieldSite ? $this->fieldSite->name : 'Global';
        $this->notifyUsers(['manager'], "Record Prepared", "A {$tableTitle} record for {$siteName} is waiting for your review.", $actingUser);

        return "Record prepared (attributed to {$sigUser->name}).";
    }

    /**
     * Mark as Reviewed. Implements:
     * - Maker-checker: cannot review if you prepared it.
     * - Attribution: If a chief/admin, attribute to site's manager.
     */
    public function markAsReviewed(User $actingUser): string|false
    {
        // Trapping: Cannot review own prepared record
        if ($this->prepared_by === $actingUser->id) {
            return false;
        }

        $sigUser = $actingUser;

        // Attribution: If a chief, try to find the site's admin
        if (in_array($actingUser->role, ['admin', 'superadmin']) && $this->field_site_id) {
            $managerUser = User::where('field_site_id', $this->field_site_id)
                ->where('role', 'manager')
                ->first();
            if ($managerUser) {
                $sigUser = $managerUser;
            }
        }

        $this->status = 'reviewed';
        $this->reviewed_by = $sigUser->id;
        $this->date_reviewed = now();
        $this->save();

        $tableTitle = class_basename($this);
        $siteName = $this->fieldSite ? $this->fieldSite->name : 'Global';
        $this->notifyUsers(['admin', 'superadmin'], "Record Reviewed", "A {$tableTitle} record for {$siteName} has been reviewed and requires noting.", $actingUser);

        return "Record reviewed (attributed to {$sigUser->name}).";
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
    public function returnToDraft(User $actingUser): string
    {
        $this->status = 'draft';
        $this->prepared_by = null;
        $this->date_prepared = null;
        $this->reviewed_by = null;
        $this->date_reviewed = null;
        $this->noted_by = null;
        $this->date_noted = null;
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
            if ($siteId && $user->field_site_id != $siteId) return false;
            return true;
        });

        if ($actingUser) {
            $users = $users->reject(fn($user) => $user->id === $actingUser->id);
        }

        foreach ($users as $user) {
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

            $notification->sendToDatabase($user);
        }
    }
}
