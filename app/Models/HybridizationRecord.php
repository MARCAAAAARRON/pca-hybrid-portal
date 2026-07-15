<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Scopes\FieldSiteScope;
use App\Models\Traits\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use App\Traits\LogsActivity;

class HybridizationRecord extends Model implements HasMedia
{
    use InteractsWithMedia, HasApprovalWorkflow, LogsActivity;

    /**
     * Average months from planting/pollination to harvest readiness.
     */
    public const HARVEST_LEAD_MONTHS = 10;

    protected static function booted(): void
    {
        static::addGlobalScope(new FieldSiteScope);
    }

    protected $fillable = [
        'field_site_id',
        'created_by',
        'crop_type',
        'parent_line_a',
        'parent_line_b',
        'hybrid_code',
        'date_planted',
        'growth_status',
        'notes',
        'status',
        'prepared_by',
        'date_prepared',
        'reviewed_by',
        'date_reviewed',
        'noted_by',
        'date_noted',
        'admin_remarks',
    ];

    public const STATUS_CHOICES = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'validated' => 'Validated',
        'revision' => 'Needs Revision',
    ];

    public const GROWTH_STATUS_CHOICES = [
        'seedling' => 'Seedling',
        'vegetative' => 'Vegetative',
        'flowering' => 'Flowering',
        'fruiting' => 'Fruiting',
        'harvested' => 'Harvested',
    ];

    protected function casts(): array
    {
        return [
            'date_planted' => 'date',
        ];
    }

    // ─── Harvest Forecasting ────────────────────────────────────

    /**
     * Estimated harvest date = date_planted + HARVEST_LEAD_MONTHS.
     */
    protected function estimatedHarvestDate(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->date_planted) {
                return null;
            }
            return $this->date_planted->copy()->addMonths(self::HARVEST_LEAD_MONTHS);
        });
    }

    /**
     * Days remaining until estimated harvest (negative = overdue).
     */
    protected function daysUntilHarvest(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->estimated_harvest_date) {
                return null;
            }
            return (int) now()->startOfDay()->diffInDays($this->estimated_harvest_date, false);
        });
    }

    /**
     * Human-readable harvest status: overdue | ready | upcoming | growing
     */
    protected function harvestStatus(): Attribute
    {
        return Attribute::get(function () {
            $days = $this->days_until_harvest;
            if ($days === null) return null;
            if ($this->growth_status === 'harvested') return 'harvested';
            if ($days < 0) return 'overdue';
            if ($days <= 7) return 'ready';
            if ($days <= 30) return 'upcoming';
            return 'growing';
        });
    }

    /**
     * Scope: records ready for harvest now (overdue or within 7 days), not yet harvested.
     */
    public function scopeReadyForHarvest(Builder $query): Builder
    {
        $cutoff = now()->addDays(7)->toDateString();
        return $query
            ->where('growth_status', '!=', 'harvested')
            ->whereNotNull('date_planted')
            ->whereRaw("date_planted + INTERVAL '" . self::HARVEST_LEAD_MONTHS . " months' <= ?", [$cutoff]);
    }

    /**
     * Scope: records with harvest within the next 30 days, not yet harvested.
     */
    public function scopeUpcomingHarvest(Builder $query): Builder
    {
        $from = now()->toDateString();
        $to   = now()->addDays(30)->toDateString();
        return $query
            ->where('growth_status', '!=', 'harvested')
            ->whereNotNull('date_planted')
            ->whereRaw("date_planted + INTERVAL '" . self::HARVEST_LEAD_MONTHS . " months' BETWEEN ? AND ?", [$from, $to]);
    }

    /**
     * Scope: not-yet-harvested records, sorted soonest first.
     */
    public function scopeNotHarvested(Builder $query): Builder
    {
        return $query->where('growth_status', '!=', 'harvested');
    }

    public function fieldSite(): BelongsTo
    {
        return $this->belongsTo(FieldSite::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(RecordImage::class);
    }

    /**
     * Register media collections for Spatie Media Library.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('field_images');
    }
}
