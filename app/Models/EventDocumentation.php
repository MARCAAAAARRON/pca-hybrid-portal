<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;

class EventDocumentation extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    public function prunable()
    {
        return static::onlyTrashed()->where('deleted_at', '<=', now()->subMonths(3));
    }

    protected $fillable = [
        'title',
        'event_date',
        'location',
        'description',
        'image_path',
        'latitude',
        'longitude',
        'captured_at',
        'uploaded_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'captured_at' => 'datetime',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
