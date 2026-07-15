<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\FieldSiteScope;
use App\Models\Traits\HasApprovalWorkflow;
use App\Models\Traits\NotifiesOnRecordCreation;

use App\Traits\LogsActivity;

class PollenProduction extends Model
{
    use HasApprovalWorkflow, NotifiesOnRecordCreation, LogsActivity;

    protected static function booted(): void
    {
        static::addGlobalScope(new FieldSiteScope);

        static::saving(function ($model) {
            if (auth()->check() && auth()->user()->isSupervisor()) {
                $model->field_site_id = auth()->user()->field_site_id;
            }
        });
    }
    protected $fillable = [
        'field_site_id',
        'upload_id',
        'report_month',
        'status',
        'prepared_by',
        'date_prepared',
        'reviewed_by',
        'date_reviewed',
        'noted_by',
        'date_noted',
        'month_label',
        'pollen_variety',
        'ending_balance_prev',
        'pollen_source',
        'date_received',
        'pollens_received',
        'week1',
        'week2',
        'week3',
        'week4',
        'week5',
        'total_utilization',
        'ending_balance',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'report_month' => 'date',
            'date_received' => 'date',
        ];
    }

    // ─── Pollen Viability & Health ──────────────────────────────

    /**
     * Calculate age of the pollen batch in days.
     * Uses `date_received` if available; falls back to `report_month`.
     */
    protected function pollenAgeDays(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(function () {
            $baseDate = $this->date_received ?? $this->report_month;
            if (!$baseDate) return null;
            return (int) now()->startOfDay()->diffInDays($baseDate, true);
        });
    }

    /**
     * Label: fresh (≤30d) | at_risk (31–60d) | expired (>60d)
     */
    protected function viabilityStatus(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(function () {
            $age = $this->pollen_age_days;
            if ($age === null) return 'unknown';
            if ($age <= 30) return 'fresh';
            if ($age <= 60) return 'at_risk';
            return 'expired';
        });
    }

    public function scopeFresh(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereRaw("CURRENT_DATE - COALESCE(date_received, report_month) <= 30");
    }

    public function scopeAtRisk(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereRaw("CURRENT_DATE - COALESCE(date_received, report_month) BETWEEN 31 AND 60");
    }

    public function scopeExpired(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereRaw("CURRENT_DATE - COALESCE(date_received, report_month) > 60");
    }

    public function scopeHasBalance(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('ending_balance', '>', 0);
    }

    public function fieldSite(): BelongsTo
    {
        return $this->belongsTo(FieldSite::class);
    }

    public function upload(): BelongsTo
    {
        return $this->belongsTo(ExcelUpload::class, 'upload_id');
    }
}
