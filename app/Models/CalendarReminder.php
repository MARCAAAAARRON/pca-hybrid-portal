<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarReminder extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'reminder_date',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'reminder_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
