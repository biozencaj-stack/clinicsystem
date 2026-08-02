<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Absence extends Model
{
    protected $fillable = [
        'doctor_id', 'date_from', 'date_to', 'reason', 'repeat_yearly',
    ];

    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',
            'repeat_yearly' => 'boolean',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** Da li odsustvo pokriva dati datum (uključuje godišnje ponavljanje). */
    public function coversDate(Carbon $date): bool
    {
        if ($this->repeat_yearly) {
            $from = $this->date_from->copy()->year($date->year);
            $to = $this->date_to->copy()->year($date->year);

            return $date->betweenIncluded($from, $to);
        }

        return $date->betweenIncluded($this->date_from, $this->date_to);
    }

    /** Odsustva koja važe za doktora na dati datum (lična + praznici klinike). */
    public static function forDoctorOn(int $doctorId, Carbon $date): Builder
    {
        return static::query()
            ->where(fn ($q) => $q->whereNull('doctor_id')->orWhere('doctor_id', $doctorId))
            ->where(function ($q) use ($date) {
                $q->where(fn ($qq) => $qq->where('repeat_yearly', false)
                    ->whereDate('date_from', '<=', $date)
                    ->whereDate('date_to', '>=', $date))
                    ->orWhere('repeat_yearly', true);
            });
    }

    /** Već zakazani termini koje ovo odsustvo pogađa. */
    public function affectedAppointments()
    {
        $query = Appointment::query()
            ->with(['patient', 'service'])
            ->whereIn('status', ['zahtev', 'zakazan', 'potvrdjen'])
            ->whereDate('starts_at', '>=', $this->date_from)
            ->whereDate('starts_at', '<=', $this->date_to);

        if ($this->doctor_id) {
            $query->where('doctor_id', $this->doctor_id);
        }

        return $query->orderBy('starts_at');
    }
}
