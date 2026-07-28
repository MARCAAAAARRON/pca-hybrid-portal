<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventDocumentation extends Model
{
    use HasFactory;

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
