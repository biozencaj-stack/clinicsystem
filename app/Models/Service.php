<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'name', 'category', 'duration_minutes', 'buffer_before', 'buffer_after',
        'price_rsd', 'preparation', 'active',
    ];

    /** Ukupno zauzeće u minutima: buffer + trajanje + buffer. */
    public function occupiedMinutes(): int
    {
        return $this->buffer_before + $this->duration_minutes + $this->buffer_after;
    }

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
