<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Models\Doctor;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use UnitEnum;

class Kalendar extends Page
{
    /** Radno vreme prikaza (sati) i razmera piksela. */
    public const START_HOUR = 7;

    public const END_HOUR = 21;

    public const PX_PER_MIN = 1.0;

    protected string $view = 'filament.pages.kalendar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = 'Zakazivanje';

    protected static ?string $navigationLabel = 'Kalendar';

    protected static ?string $title = 'Kalendar';

    protected static ?int $navigationSort = 0;

    public int $weekOffset = 0;

    public ?string $doctorId = null;

    public function previousWeek(): void
    {
        $this->weekOffset--;
    }

    public function nextWeek(): void
    {
        $this->weekOffset++;
    }

    public function currentWeek(): void
    {
        $this->weekOffset = 0;
    }

    public function getWeekStart(): Carbon
    {
        return now()->startOfWeek()->addWeeks($this->weekOffset);
    }

    protected function getViewData(): array
    {
        $weekStart = $this->getWeekStart();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $dayStartMin = self::START_HOUR * 60;
        $dayEndMin = self::END_HOUR * 60;

        $appointments = Appointment::query()
            ->with(['patient', 'doctor', 'service'])
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->when($this->doctorId, fn ($q) => $q->where('doctor_id', $this->doctorId))
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Appointment $a) => $a->starts_at->toDateString());

        $days = collect(range(0, 6))->map(function (int $i) use ($weekStart, $appointments, $dayStartMin, $dayEndMin) {
            $date = $weekStart->copy()->addDays($i);

            return [
                'date' => $date,
                'isToday' => $date->isToday(),
                'events' => $this->layoutEvents(
                    $appointments->get($date->toDateString(), collect()),
                    $dayStartMin,
                    $dayEndMin,
                ),
            ];
        });

        $nowOffset = null;
        $nowMin = now()->hour * 60 + now()->minute;
        if ($nowMin >= $dayStartMin && $nowMin <= $dayEndMin) {
            $nowOffset = ($nowMin - $dayStartMin) * self::PX_PER_MIN;
        }

        return [
            'days' => $days,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'doctors' => Doctor::where('active', true)->orderBy('name')->get(),
            'statusLabels' => Appointment::STATUSES,
            'hours' => range(self::START_HOUR, self::END_HOUR),
            'gridHeight' => ($dayEndMin - $dayStartMin) * self::PX_PER_MIN,
            'nowOffset' => $nowOffset,
        ];
    }

    /**
     * Raspoređuje termine u blokove: vertikalno po vremenu, horizontalno u
     * kolone ("lanes") kada se preklapaju — kao u Google kalendaru.
     */
    protected function layoutEvents(Collection $dayAppointments, int $dayStartMin, int $dayEndMin): array
    {
        $events = $dayAppointments->map(function (Appointment $a) use ($dayStartMin, $dayEndMin) {
            $start = max($a->starts_at->hour * 60 + $a->starts_at->minute, $dayStartMin);
            $ends = $a->ends_at ?? $a->starts_at->copy()->addMinutes(30);
            $end = min(max($ends->hour * 60 + $ends->minute, $start + 24), $dayEndMin);

            return [
                'a' => $a,
                'startMin' => $start,
                'endMin' => $end,
                'top' => ($start - $dayStartMin) * self::PX_PER_MIN,
                'height' => max(($end - $start) * self::PX_PER_MIN, 26),
                'lane' => 0,
                'laneCount' => 1,
            ];
        })->sortBy('startMin')->values()->all();

        // Grupisanje u klastere međusobno preklopljenih termina.
        $clusters = [];
        $current = [];
        $clusterEnd = -1;
        foreach ($events as $idx => $e) {
            if ($current !== [] && $e['startMin'] >= $clusterEnd) {
                $clusters[] = $current;
                $current = [];
                $clusterEnd = -1;
            }
            $current[] = $idx;
            $clusterEnd = max($clusterEnd, $e['endMin']);
        }
        if ($current !== []) {
            $clusters[] = $current;
        }

        // Unutar klastera: pohlepno dodeljivanje kolona.
        foreach ($clusters as $cluster) {
            $laneEnds = [];
            foreach ($cluster as $idx) {
                $placed = false;
                foreach ($laneEnds as $lane => $end) {
                    if ($events[$idx]['startMin'] >= $end) {
                        $events[$idx]['lane'] = $lane;
                        $laneEnds[$lane] = $events[$idx]['endMin'];
                        $placed = true;
                        break;
                    }
                }
                if (! $placed) {
                    $events[$idx]['lane'] = count($laneEnds);
                    $laneEnds[] = $events[$idx]['endMin'];
                }
            }
            foreach ($cluster as $idx) {
                $events[$idx]['laneCount'] = count($laneEnds);
            }
        }

        return $events;
    }
}
