<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\FieldSiteScope;
use App\Models\Traits\HasApprovalWorkflow;
use App\Models\Traits\NotifiesOnRecordCreation;

class MonthlyHarvest extends Model
{
    use HasApprovalWorkflow, NotifiesOnRecordCreation;

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
        'location',
        'farm_name',
        'area_ha',
        'age_of_palms',
        'num_hybridized_palms',
        'variety',
        'seednuts_produced',
        'production_jan',
        'production_feb',
        'production_mar',
        'production_apr',
        'production_may',
        'production_jun',
        'production_jul',
        'production_aug',
        'production_sep',
        'production_oct',
        'production_nov',
        'production_dec',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'report_month' => 'date',
            'num_hybridized_palms' => 'integer',
            'production_jan' => 'integer',
            'production_feb' => 'integer',
            'production_mar' => 'integer',
            'production_apr' => 'integer',
            'production_may' => 'integer',
            'production_jun' => 'integer',
            'production_jul' => 'integer',
            'production_aug' => 'integer',
            'production_sep' => 'integer',
            'production_oct' => 'integer',
            'production_nov' => 'integer',
            'production_dec' => 'integer',
        ];
    }

    public function fieldSite(): BelongsTo
    {
        return $this->belongsTo(FieldSite::class);
    }

    public function upload(): BelongsTo
    {
        return $this->belongsTo(ExcelUpload::class, 'upload_id');
    }

    public function varieties(): HasMany
    {
        return $this->hasMany(HarvestVariety::class);
    }

    /**
     * Total seednuts from all varieties.
     */
    public function getTotalSeednutsAttribute(): int
    {
        return $this->varieties->sum('seednuts_count');
    }

    /**
     * Total of all varieties.
     */
    public function getTotalProductionAttribute(): int
    {
        return $this->varieties->sum('seednuts_count');
    }
}
