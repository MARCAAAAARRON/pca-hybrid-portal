<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\FieldSiteScope;
use App\Models\Traits\HasApprovalWorkflow;
use App\Models\Traits\NotifiesOnRecordCreation;

class HybridDistribution extends Model
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
        'region',
        'province',
        'district',
        'municipality',
        'barangay',
        'farmer_last_name',
        'farmer_first_name',
        'farmer_middle_initial',
        'is_male',
        'is_female',
        'farm_barangay',
        'farm_municipality',
        'farm_province',
        'seedlings_received',
        'date_received',
        'variety',
        'seedlings_planted',
        'date_planted',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'report_month' => 'date',
            'date_received' => 'date',
            'date_planted' => 'date',
            'is_male' => 'boolean',
            'is_female' => 'boolean',
            'seedlings_planted' => 'integer',
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

    /**
     * Get the farmer's full name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->farmer_last_name,
            $this->farmer_first_name,
            $this->farmer_middle_initial,
        ]);

        return implode(', ', $parts);
    }
}
