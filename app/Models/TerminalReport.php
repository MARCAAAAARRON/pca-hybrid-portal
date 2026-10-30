<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TerminalReport extends NurseryOperation
{
    protected $table = 'nursery_operations';

    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('terminal_report_type', function ($builder) {
            $builder->where('report_type', 'terminal');
        });

        static::saving(function ($model) {
            $model->report_type = 'terminal';
        });
    }

    public function batches(): HasMany
    {
        return $this->hasMany(NurseryBatch::class, 'nursery_operation_id');
    }
}

