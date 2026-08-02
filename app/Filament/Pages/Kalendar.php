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

    public const MODES = [
        'dan' => 'Dan',
        'nedelja' => 'Nedelja',
        'mesec' => 'Mesec',
        'lista' => 'Lista',
    ];

    protected string $view = 'filament.pages.kalendar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = 'Zakazivanje';

    protected static ?string $navigationLabel = 'Kalendar';

    protected static ?string $title = 'Kalendar';

    protected static ?int $navigationSort = 0;

    public string $mode = 'nedelja';

    public string $anchorDate = '';

    public string $pickerMonth = '';

    /** @var array<int, string> */
    public array $doctorIds = [];

    public function mount(): void
    {
        $this->mode = session('kalendar.mode', 'nedelja');
        $this->doctorIds = session('kalendar.doctors', []);
        $this->anchorDate = today()->toDateString();
        $this->pickerMonth = today()->startOfMonth()->toDateString();
    }

    public function updatedDoctorIds(): void
    {
        session(['kalendar.doctors' => $this->doctorIds]);
    }

    public function allDoctors(): void
    {
        $this->doctorIds = [];
        session(['kalendar.doctors' => []]);
    }

    public function setMode(string $mode): void
    {
        if (array_key_exists($mode, self::MODES)) {
            $this->mode = $mode;
            session(['kalendar.mode' => $mode]);
        }
    }

    public function previous(): void
    {
        $this->anchorDate = $this->shiftAnchor(-1)->toDateString();
        $this->syncPickerToAnchor();
    }

    public function next(): void
    {
        $this->anchorDate = $this->shiftAnchor(1)->toDateString();
        $this->syncPickerToAnchor();
    }

    public function today(): void
    {
        $this->anchorDate = today()->toDateString();
        $this->syncPickerToAnchor();
        $this->dispatch('kalendar-scroll-today');
    }

    public function goTo(string $date): void
    {
        $this->anchorDate = Carbon::parse($date)->toDateString();
        $this->syncPickerToAnchor();
    }

    public function pickerPrev(): void
    {
        $this->pickerMonth = Carbon::parse($this->pickerMonth)->subMonthNoOverflow()->startOfMonth()->toDateString();
    }

    public function pickerNext(): void
    {
        $this->pickerMonth = Carbon::parse($this->pickerMonth)->addMonthNoOverflow()->startOfMonth()->toDateString();
    }

    protected function syncPickerToAnchor(): void
    {
        $this->pickerMonth = Carbon::parse($this->anchorDate)->startOfMonth()->toDateString();
    }

    protected function doctorFilterLabel(Collection $doctors): string
    {
        if ($this->doctorIds === []) {
            return 'Svi doktori';
        }

        $selected = $doctors->whereIn('id', array_map('intval', $this->doctorIds));

        return $selected->count() === 1
            ? $selected->first()->full_name
            : $selected->count() . ' doktora';
    }

    /** Podaci za mini kalendar (popover za izbor datuma). */
    protected function pickerData(): array
    {
        $month = Carbon::parse($this->pickerMonth);
        $gridStart = $month->copy()->startOfWeek();
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek();
        $anchor = Carbon::parse($this->anchorDate);

        $weeks = [];
        $cursor = $gridStart->copy();
        while ($cursor <= $gridEnd) {
            $week = [];
            foreach (range(0, 6) as $i) {
                $date = $cursor->copy()->addDays($i);
                $week[] = [
                    'date' => $date->toDateString(),
                    'day' => $date->day,
                    'inMonth' => $date->month === $month->month,
                    'isToday' => $date->isToday(),
                    'isAnchor' => $date->isSameDay($anchor),
                ];
            }
            $weeks[] = $week;
            $cursor->addWeek();
        }

        $monthNames = ['januar', 'februar', 'mart', 'april', 'maj', 'jun', 'jul', 'avgust', 'septembar', 'oktobar', 'novembar', 'decembar'];

        return [
            'weeks' => $weeks,
            'label' => $monthNames[$month->month - 1] . ' ' . $month->year . '.',
        ];
    }

    protected function shiftAnchor(int $direction): Carbon
    {
        $anchor = Carbon::parse($this->anchorDate);

        return match ($this->mode) {
            'dan' => $anchor->addDays($direction),
            'mesec' => $anchor->addMonthsNoOverflow($direction)->startOfMonth(),
            default => $anchor->addWeeks($direction),
        };
    }

    protected function getViewData(): array
    {
        $anchor = Carbon::parse($this->anchorDate);
        $doctors = Doctor::where('active', true)->orderBy('name')->get();

        $data = match ($this->mode) {
            'dan' => $this->dayData($anchor, $doctors),
            'mesec' => $this->monthData($anchor),
            'lista' => $this->listData($anchor),
            default => $this->weekData($anchor),
        };

        return $data + [
            'doctors' => $doctors,
            'statusLabels' => Appointment::STATUSES,
            'modes' => self::MODES,
            'hours' => range(self::START_HOUR, self::END_HOUR),
            'gridHeight' => (self::END_HOUR - self::START_HOUR) * 60 * self::PX_PER_MIN,
            'nowOffset' => $this->nowOffset(),
            'picker' => $this->pickerData(),
            'doctorFilterLabel' => $this->doctorFilterLabel($doctors),
        ];
    }

    protected function nowOffset(): ?float
    {
        $nowMin = now()->hour * 60 + now()->minute;
        $start = self::START_HOUR * 60;

        return ($nowMin >= $start && $nowMin <= self::END_HOUR * 60)
            ? ($nowMin - $start) * self::PX_PER_MIN
            : null;
    }

    protected function baseQuery()
    {
        return Appointment::query()
            ->with(['patient', 'doctor', 'service'])
            ->when($this->doctorIds !== [], fn ($q) => $q->whereIn('doctor_id', $this->doctorIds))
            ->orderBy('starts_at');
    }

    /** Nedelja: 7 kolona dana sa vremenskom osom. */
    protected function weekData(Carbon $anchor): array
    {
        $weekStart = $anchor->copy()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $appointments = $this->baseQuery()
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->get()
            ->groupBy(fn (Appointment $a) => $a->starts_at->toDateString());

        $days = collect(range(0, 6))->map(function (int $i) use ($weekStart, $appointments) {
            $date = $weekStart->copy()->addDays($i);

            return [
                'date' => $date,
                'isToday' => $date->isToday(),
                'events' => $this->layoutEvents($appointments->get($date->toDateString(), collect())),
            ];
        });

        return [
            'days' => $days,
            'rangeLabel' => $weekStart->format('d.m.') . ' — ' . $weekEnd->format('d.m.Y.'),
        ];
    }

    /** Dan: jedna kolona po doktoru (resursni prikaz). */
    protected function dayData(Carbon $anchor, Collection $doctors): array
    {
        $appointments = $this->baseQuery()
            ->whereDate('starts_at', $anchor)
            ->get()
            ->groupBy('doctor_id');

        $visibleDoctors = $this->doctorIds !== []
            ? $doctors->whereIn('id', array_map('intval', $this->doctorIds))->values()
            : $doctors;

        $columns = $visibleDoctors->map(fn (Doctor $d) => [
            'doctor' => $d,
            'events' => $this->layoutEvents($appointments->get($d->id, collect())),
        ]);

        $dayNames = ['ponedeljak', 'utorak', 'sreda', 'četvrtak', 'petak', 'subota', 'nedelja'];

        return [
            'columns' => $columns,
            'day' => $anchor,
            'isToday' => $anchor->isToday(),
            'rangeLabel' => $dayNames[$anchor->dayOfWeekIso - 1] . ', ' . $anchor->format('d.m.Y.'),
        ];
    }

    /** Mesec: mreža nedelja sa kompaktnim terminima. */
    protected function monthData(Carbon $anchor): array
    {
        $monthStart = $anchor->copy()->startOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek();
        $gridEnd = $anchor->copy()->endOfMonth()->endOfWeek();

        $appointments = $this->baseQuery()
            ->whereBetween('starts_at', [$gridStart, $gridEnd])
            ->get()
            ->groupBy(fn (Appointment $a) => $a->starts_at->toDateString());

        $weeks = [];
        $cursor = $gridStart->copy();
        while ($cursor <= $gridEnd) {
            $week = [];
            foreach (range(0, 6) as $i) {
                $date = $cursor->copy()->addDays($i);
                $week[] = [
                    'date' => $date,
                    'isToday' => $date->isToday(),
                    'inMonth' => $date->month === $monthStart->month,
                    'appointments' => $appointments->get($date->toDateString(), collect()),
                ];
            }
            $weeks[] = $week;
            $cursor->addWeek();
        }

        $monthNames = ['januar', 'februar', 'mart', 'april', 'maj', 'jun', 'jul', 'avgust', 'septembar', 'oktobar', 'novembar', 'decembar'];

        return [
            'weeks' => $weeks,
            'rangeLabel' => $monthNames[$monthStart->month - 1] . ' ' . $monthStart->year . '.',
        ];
    }

    /** Lista: hronološki po danima izabrane nedelje. */
    protected function listData(Carbon $anchor): array
    {
        $weekStart = $anchor->copy()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $dayNames = ['ponedeljak', 'utorak', 'sreda', 'četvrtak', 'petak', 'subota', 'nedelja'];

        $groups = $this->baseQuery()
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->get()
            ->groupBy(fn (Appointment $a) => $a->starts_at->toDateString())
            ->map(fn ($items, $key) => [
                'date' => Carbon::parse($key),
                'dayName' => $dayNames[Carbon::parse($key)->dayOfWeekIso - 1],
                'isToday' => Carbon::parse($key)->isToday(),
                'appointments' => $items,
            ])
            ->values();

        return [
            'groups' => $groups,
            'rangeLabel' => $weekStart->format('d.m.') . ' — ' . $weekEnd->format('d.m.Y.'),
        ];
    }

    /**
     * Raspoređuje termine u blokove: vertikalno po vremenu, horizontalno u
     * kolone ("lanes") kada se preklapaju.
     */
    protected function layoutEvents(Collection $dayAppointments): array
    {
        $dayStartMin = self::START_HOUR * 60;
        $dayEndMin = self::END_HOUR * 60;

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
