<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;

class Report extends Model
{
    use SoftDeletes, Prunable;

    public function prunable()
    {
        return static::onlyTrashed()->where('deleted_at', '<=', now()->subMonths(3));
    }

    protected $fillable = [
        'generated_by',
        'report_type',
        'field_site_id',
        'title',
        'file',
    ];

    public const REPORT_TYPES = [
        'pdf' => 'PDF Report',
        'csv' => 'CSV Export',
        'excel' => 'Excel Export',
    ];

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function fieldSite(): BelongsTo
    {
        return $this->belongsTo(FieldSite::class);
    }
}
