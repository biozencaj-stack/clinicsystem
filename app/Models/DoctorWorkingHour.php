<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorWorkingHour extends Model
{
    public const WEEKDAYS = [
        1 => 'Ponedeljak',
        2 => 'Utorak',
        3 => 'Sreda',
        4 => 'Četvrtak',
        5 => 'Petak',
        6 => 'Subota',
        7 => 'Nedelja',
    ];

    protected $fillable = [
        'doctor_id', 'weekday', 'starts_at', 'ends_at', 'service_ids',
    ];

    protected function casts(): array
    {
        return ['service_ids' => 'array'];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** Da li ovaj period dozvoljava datu uslugu (null = sve usluge). */
    public function allowsService(int $serviceId): bool
    {
        return blank($this->service_ids) || in_array($serviceId, array_map('intval', $this->service_ids));
    }
}
