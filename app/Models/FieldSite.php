<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldSite extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'prepared_by_label',
        'prepared_by_name',
        'prepared_by_title',
        'reviewed_by_label',
        'reviewed_by_name',
        'reviewed_by_title',
        'noted_by_label',
        'noted_by_name',
        'noted_by_title',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(HybridDistribution::class);
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(MonthlyHarvest::class);
    }

    public function nurseryOperations(): HasMany
    {
        return $this->hasMany(NurseryOperation::class);
    }

    public function pollenRecords(): HasMany
    {
        return $this->hasMany(PollenProduction::class);
    }

    public function hybridizationRecords(): HasMany
    {
        return $this->hasMany(HybridizationRecord::class);
    }

    public function excelUploads(): HasMany
    {
        return $this->hasMany(ExcelUpload::class);
    }

    protected static function booted(): void
    {
        // Fix #2: Prevent unique constraint collision on the 'name' column
        static::deleting(function (FieldSite $site) {
            if (!$site->isForceDeleting()) {
                $site->name = $site->name . '.deleted.' . time();
                $site->saveQuietly();

                // Fix #4: Cascade soft-delete to associated users
                $site->users()->each(function ($user) {
                    $user->delete();
                });
            }
        });

        // On restore, clean up the name suffix and restore associated users
        static::restoring(function (FieldSite $site) {
            $site->name = preg_replace('/\.deleted\.\d+$/', '', $site->name);
        });

        static::restored(function (FieldSite $site) {
            // Restore users that were cascade-deleted with this site
            $site->users()->onlyTrashed()->each(function ($user) {
                $user->restore();
            });
        });
    }
}
