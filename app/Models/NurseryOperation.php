<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\FieldSiteScope;
use App\Models\Traits\HasApprovalWorkflow;
use App\Models\Traits\NotifiesOnRecordCreation;

class NurseryOperation extends Model
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
        'report_type',
        'status',
        'prepared_by',
        'date_prepared',
        'reviewed_by',
        'date_reviewed',
        'noted_by',
        'date_noted',
        'region_province_district',
        'barangay_municipality',
        'proponent_entity',
        'proponent_representative',
        'target_seednuts',
        'nursery_start_date',
        'date_ready_for_distribution',
    ];

    public const REPORT_TYPES = [
        'operation' => 'Monthly Report',
        'terminal' => 'Terminal Report',
    ];

    protected function casts(): array
    {
        return [
            'report_month' => 'date',
            'target_seednuts' => 'integer',
            'nursery_start_date' => 'date',
            'date_ready_for_distribution' => 'date',
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

    public function batches(): HasMany
    {
        return $this->hasMany(NurseryBatch::class);
    }
}
