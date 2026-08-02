<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    public const CHANNELS = [
        'whatsapp' => 'WhatsApp',
        'viber' => 'Viber',
        'email' => 'E-mail',
    ];

    public const TYPES = [
        'potvrda' => 'Potvrda termina',
        'podsetnik' => 'Podsetnik (24h)',
        'nalaz' => 'Nalaz spreman',
        'izmena' => 'Izmena (doktoru)',
        'bot' => 'AI bot razgovor',
    ];

    public const STATUSES = [
        'simulirano' => 'Simulirano (demo)',
        'zakazano' => 'Zakazano za slanje',
        'poslato' => 'Poslato',
        'isporuceno' => 'Isporučeno',
        'procitano' => 'Pročitano',
    ];

    protected $fillable = [
        'patient_id', 'doctor_id', 'direction', 'channel', 'type', 'destination',
        'body', 'status', 'scheduled_for', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Kanal po prioritetu: WhatsApp → Viber → e-mail. Null ako nema saglasnosti.
     *
     * @return array{0: string, 1: string}|null [kanal, destinacija]
     */
    public static function resolveChannel(Patient $patient): ?array
    {
        return match (true) {
            (bool) $patient->whatsapp_opt_in => ['whatsapp', $patient->phone],
            (bool) $patient->viber_opt_in => ['viber', $patient->phone],
            $patient->email_opt_in && filled($patient->email) => ['email', $patient->email],
            default => null,
        };
    }

    public static function sendConfirmation(Appointment $a): void
    {
        $a->loadMissing(['patient', 'doctor', 'service']);
        if (! $a->patient || ! ($route = static::resolveChannel($a->patient))) {
            return;
        }

        static::create([
            'patient_id' => $a->patient_id,
            'channel' => $route[0],
            'type' => 'potvrda',
            'destination' => $route[1],
            'body' => "Poštovani/a {$a->patient->full_name}, Vaš termin je zakazan: {$a->service->name}, "
                . $a->starts_at->format('d.m.Y.') . ' u ' . $a->starts_at->format('H:i')
                . " kod {$a->doctor->full_name}. Za otkazivanje odgovorite OTKAZUJEM. — Poliklinika MagnaMed",
            'status' => 'simulirano',
            'sent_at' => now(),
        ]);
    }

    public static function scheduleReminder(Appointment $a): void
    {
        $a->loadMissing(['patient', 'doctor', 'service']);
        if (! $a->patient || ! ($route = static::resolveChannel($a->patient))) {
            return;
        }

        static::create([
            'patient_id' => $a->patient_id,
            'channel' => $route[0],
            'type' => 'podsetnik',
            'destination' => $route[1],
            'body' => 'Podsetnik: sutra u ' . $a->starts_at->format('H:i')
                . " imate zakazan pregled ({$a->service->name}) kod {$a->doctor->full_name}."
                . ($a->service->preparation ? " Priprema: {$a->service->preparation}" : '')
                . ' Molimo odgovorite POTVRĐUJEM ili OTKAZUJEM. — Poliklinika MagnaMed',
            'status' => 'zakazano',
            'scheduled_for' => $a->starts_at->copy()->subDay(),
        ]);
    }

    public static function sendNalazReady(Nalaz $n): void
    {
        $n->loadMissing('patient');
        if (! $n->patient || ! ($route = static::resolveChannel($n->patient))) {
            return;
        }

        static::create([
            'patient_id' => $n->patient_id,
            'channel' => $route[0],
            'type' => 'nalaz',
            'destination' => $route[1],
            'body' => "Poštovani/a {$n->patient->full_name}, Vaš nalaz „{$n->title}“ je spreman. "
                . "Preuzmite ga bezbedno na: {$n->downloadUrl()} (link važi 7 dana). — Poliklinika MagnaMed",
            'status' => 'simulirano',
            'sent_at' => now(),
        ]);

        $n->forceFill(['ready_notified_at' => now()])->saveQuietly();
    }
}
