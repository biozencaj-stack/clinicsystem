<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Support\Carbon;

/**
 * Motor slobodnih termina. Redosled provera:
 * odsustva (praznik/odmor) → radno vreme za taj dan (filtrirano po usluzi)
 * → postojeći termini (sa bufferima) → pravila za pacijentske kanale.
 */
class AvailabilityService
{
    /**
     * Slobodni početni termini za doktora, uslugu i datum.
     *
     * @return array<int, Carbon> počeci vidljivog dela termina
     */
    public function slots(Doctor $doctor, Service $service, Carbon $date, bool $forPatientChannel = false): array
    {
        $date = $date->copy()->startOfDay();

        if ($forPatientChannel && $date->gt(today()->addDays(config('clinic.horizon_days')))) {
            return [];
        }

        // 1. Odsustva: praznik klinike ili odmor doktora blokira ceo dan.
        $absent = Absence::forDoctorOn($doctor->id, $date)
            ->get()
            ->contains(fn (Absence $a) => $a->coversDate($date));

        if ($absent) {
            return [];
        }

        // 2. Poseban dan (izmena rasporeda za datum) ima prednost nad nedeljnim rasporedom.
        $override = \App\Models\DoctorScheduleOverride::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', $date)
            ->first();

        if ($override) {
            $periods = collect($override->periodsForService($service->id))
                ->map(fn ($p) => ['starts_at' => $p['starts_at'], 'ends_at' => $p['ends_at']]);
        } else {
            $periods = $doctor->workingHours
                ->where('weekday', $date->dayOfWeekIso)
                ->filter(fn ($p) => $p->allowsService($service->id))
                ->map(fn ($p) => ['starts_at' => $p->starts_at, 'ends_at' => $p->ends_at]);
        }

        if ($periods->isEmpty()) {
            return [];
        }

        // 3. Zauzeti blokovi postojećih termina (uključujući njihove buffere).
        $busy = Appointment::query()
            ->with('service')
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', Appointment::BLOCKING_STATUSES)
            ->whereDate('starts_at', $date)
            ->get()
            ->map(function (Appointment $a) {
                $before = $a->service?->buffer_before ?? 0;
                $after = $a->service?->buffer_after ?? 0;

                return [
                    'from' => $a->starts_at->copy()->subMinutes($before),
                    'to' => ($a->ends_at ?? $a->starts_at->copy()->addMinutes(30))->copy()->addMinutes($after),
                ];
            });

        $slots = [];

        foreach ($periods as $period) {
            $periodStart = $date->copy()->setTimeFromTimeString($period['starts_at']);
            $periodEnd = $date->copy()->setTimeFromTimeString($period['ends_at']);

            // Koračanje po trajanju usluge — čisti termini bez zalutalih rupa.
            $cursor = $periodStart->copy()->addMinutes($service->buffer_before);

            while (true) {
                $visibleEnd = $cursor->copy()->addMinutes($service->duration_minutes);
                $blockFrom = $cursor->copy()->subMinutes($service->buffer_before);
                $blockTo = $visibleEnd->copy()->addMinutes($service->buffer_after);

                if ($blockTo->gt($periodEnd) || $blockFrom->lt($periodStart)) {
                    break;
                }

                $conflicts = $busy->contains(fn ($b) => $blockFrom->lt($b['to']) && $blockTo->gt($b['from']));

                $tooSoon = $forPatientChannel
                    && $cursor->lt(now()->addHours(config('clinic.min_book_hours')));

                if (! $conflicts && ! $tooSoon && $cursor->gte(now())) {
                    $slots[] = $cursor->copy();
                }

                $cursor->addMinutes($service->occupiedMinutes());
            }
        }

        usort($slots, fn ($a, $b) => $a <=> $b);

        return $slots;
    }

    /** Da li pacijent sme (linkom) da otkaže termin — pravilo min_cancel_hours. */
    public function patientCanCancel(Appointment $appointment): bool
    {
        return now()->lt(
            $appointment->starts_at->copy()->subHours(config('clinic.min_cancel_hours'))
        );
    }
}
