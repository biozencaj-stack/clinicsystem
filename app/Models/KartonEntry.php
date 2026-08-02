<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartonEntry extends Model
{
    public const TYPES = [
        'anamneza' => 'Anamneza',
        'pregled' => 'Pregled',
        'dijagnoza' => 'Dijagnoza',
        'terapija' => 'Terapija',
        'kontrola' => 'Kontrola',
        'napomena' => 'Napomena',
    ];

    protected $fillable = [
        'patient_id', 'doctor_id', 'appointment_id', 'entry_date',
        'type', 'diagnosis_code', 'title', 'content', 'download_token',
    ];

    protected function casts(): array
    {
        return ['entry_date' => 'date'];
    }

    protected static function booted(): void
    {
        static::creating(function (KartonEntry $entry) {
            $entry->download_token ??= \Illuminate\Support\Str::random(40);
        });
    }

    public function downloadUrl(): string
    {
        if (blank($this->download_token)) {
            $this->forceFill(['download_token' => \Illuminate\Support\Str::random(40)])->saveQuietly();
        }

        return url("/izvestaj/{$this->download_token}");
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
