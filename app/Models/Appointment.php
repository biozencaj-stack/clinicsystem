<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    public const STATUSES = [
        'zahtev' => 'Zahtev (čeka potvrdu)',
        'zakazan' => 'Zakazan',
        'potvrdjen' => 'Potvrđen od pacijenta',
        'zavrsen' => 'Završen',
        'otkazan' => 'Otkazan',
        'nije_dosao' => 'Nije došao',
    ];

    public const SOURCES = [
        'recepcija' => 'Recepcija',
        'sajt' => 'Sajt (magnamed.rs)',
        'whatsapp' => 'WhatsApp bot',
        'telefon' => 'Telefon',
    ];

    protected $fillable = [
        'patient_id', 'doctor_id', 'service_id', 'starts_at', 'ends_at',
        'status', 'source', 'note',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $a) {
            if (! $a->ends_at && $a->starts_at) {
                $service = Service::find($a->service_id);
                if ($service) {
                    $a->ends_at = $a->starts_at->copy()->addMinutes($service->duration_minutes);
                }
            }
        });

        static::created(function (Appointment $a) {
            if (in_array($a->status, ['zakazan', 'potvrdjen'])) {
                WhatsappMessage::sendConfirmation($a);
                WhatsappMessage::scheduleReminder($a);
            }
        });

        static::updated(function (Appointment $a) {
            if ($a->wasChanged('status') && $a->status === 'zakazan' && $a->getOriginal('status') === 'zahtev') {
                WhatsappMessage::sendConfirmation($a);
                WhatsappMessage::scheduleReminder($a);
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
