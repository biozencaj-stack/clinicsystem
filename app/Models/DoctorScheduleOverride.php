<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorScheduleOverride extends Model
{
    protected $fillable = [
        'doctor_id', 'date', 'reason', 'periods',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'periods' => 'array',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** Periodi koji dozvoljavaju datu uslugu (service_ids null = sve). */
    public function periodsForService(int $serviceId): array
    {
        return array_values(array_filter($this->periods ?? [], function ($period) use ($serviceId) {
            $ids = $period['service_ids'] ?? null;

            return blank($ids) || in_array($serviceId, array_map('intval', $ids));
        }));
    }
}
