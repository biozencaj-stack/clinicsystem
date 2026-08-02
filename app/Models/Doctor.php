<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Doctor extends Model
{
    protected $fillable = [
        'title', 'name', 'specialty', 'phone', 'email', 'ics_token', 'color', 'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Doctor $doctor) {
            $doctor->ics_token ??= Str::random(40);
        });
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->title} {$this->name}");
    }

    public function icsUrl(): string
    {
        return url("/kalendar/{$this->ics_token}.ics");
    }
}
