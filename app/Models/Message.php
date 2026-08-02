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
        'dokument' => 'Dokument pacijentu',
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

    /** Placeholder vrednosti iz termina za render šablona. */
    protected static function appointmentVars(Appointment $a): array
    {
        return [
            '%pacijent_ime%' => $a->patient?->full_name ?? '',
            '%usluga%' => $a->service?->name ?? '',
            '%datum%' => $a->starts_at->format('d.m.Y.'),
            '%vreme%' => $a->starts_at->format('H:i'),
            '%doktor%' => $a->doctor?->full_name ?? '',
            '%priprema%' => $a->service?->preparation ? "Priprema: {$a->service->preparation}" : '',
            '%potvrdi_link%' => $a->confirmUrl(),
            '%otkazi_link%' => $a->cancelUrl(),
            '%telefon_klinike%' => config('clinic.phone'),
        ];
    }

    public static function sendConfirmation(Appointment $a): void
    {
        $a->loadMissing(['patient', 'doctor', 'service']);
        if (! $a->patient || ! ($route = static::resolveChannel($a->patient))) {
            return;
        }

        $template = MessageTemplate::resolve('potvrda', $a->service_id);

        static::create([
            'patient_id' => $a->patient_id,
            'channel' => $route[0],
            'type' => 'potvrda',
            'destination' => $route[1],
            'body' => MessageTemplate::render($template['body'], static::appointmentVars($a)),
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

        $template = MessageTemplate::resolve('podsetnik', $a->service_id);
        $offsetHours = $template['offset_hours'] ?? 24;

        static::create([
            'patient_id' => $a->patient_id,
            'channel' => $route[0],
            'type' => 'podsetnik',
            'destination' => $route[1],
            'body' => MessageTemplate::render($template['body'], static::appointmentVars($a)),
            'status' => 'zakazano',
            'scheduled_for' => $a->starts_at->copy()->subHours($offsetHours),
        ]);
    }

    /** Poruka pacijentu kada je zahtev za termin odbijen. */
    public static function sendRejection(Appointment $a): void
    {
        $a->loadMissing(['patient', 'doctor', 'service']);
        if (! $a->patient || ! ($route = static::resolveChannel($a->patient))) {
            return;
        }

        $template = MessageTemplate::resolve('odbijen', $a->service_id);

        static::create([
            'patient_id' => $a->patient_id,
            'channel' => $route[0],
            'type' => 'potvrda',
            'destination' => $route[1],
            'body' => MessageTemplate::render($template['body'], static::appointmentVars($a)),
            'status' => 'simulirano',
            'sent_at' => now(),
        ]);
    }

    /**
     * Šalje pacijentu bezbedan link ka dokumentu preko njegovog kanala.
     * Vraća null ako pacijent nema nijednu saglasnost.
     */
    public static function sendDocument(Patient $patient, string $title, string $url, string $type = 'dokument'): ?self
    {
        if (! ($route = static::resolveChannel($patient))) {
            return null;
        }

        $template = MessageTemplate::resolve($type === 'nalaz' ? 'nalaz' : 'dokument');

        return static::create([
            'patient_id' => $patient->id,
            'channel' => $route[0],
            'type' => $type,
            'destination' => $route[1],
            'body' => MessageTemplate::render($template['body'], [
                '%pacijent_ime%' => $patient->full_name,
                '%naziv_dokumenta%' => $title,
                '%dokument_link%' => $url,
                '%telefon_klinike%' => config('clinic.phone'),
            ]),
            'status' => 'simulirano',
            'sent_at' => now(),
        ]);
    }

    public static function sendNalazReady(Nalaz $n): void
    {
        $n->loadMissing('patient');
        if (! $n->patient) {
            return;
        }

        if (static::sendDocument($n->patient, $n->title, $n->downloadUrl(), 'nalaz')) {
            $n->forceFill(['ready_notified_at' => now()])->saveQuietly();
        }
    }
}
