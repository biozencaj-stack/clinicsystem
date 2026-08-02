<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Models\Doctor;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use UnitEnum;

class IzvestajRada extends Page
{
    public const MONTH_NAMES = [
        'januar', 'februar', 'mart', 'april', 'maj', 'jun',
        'jul', 'avgust', 'septembar', 'oktobar', 'novembar', 'decembar',
    ];

    protected string $view = 'filament.pages.izvestaj-rada';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Zakazivanje';

    protected static ?string $navigationLabel = 'Izveštaj rada';

    protected static ?string $title = 'Izveštaj rada';

    protected static ?int $navigationSort = 5;

    public int $monthOffset = 0;

    public static function canAccess(): bool
    {
        return ! auth()->user()?->isDoctor();
    }

    public function previousMonth(): void
    {
        $this->monthOffset--;
    }

    public function nextMonth(): void
    {
        $this->monthOffset++;
    }

    public function currentMonth(): void
    {
        $this->monthOffset = 0;
    }

    public function getMonth(): Carbon
    {
        return now()->startOfMonth()->addMonthsNoOverflow($this->monthOffset);
    }

    /**
     * Mesečni obračun: po doktoru — završene usluge (broj × cena), nedolasci, otkazivanja.
     *
     * @return array{doctors: array, total: array, month: Carbon}
     */
    public static function buildReport(Carbon $month): array
    {
        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $appointments = Appointment::query()
            ->with(['service', 'doctor'])
            ->whereBetween('starts_at', [$from, $to])
            ->get()
            ->groupBy('doctor_id');

        $doctors = Doctor::orderBy('name')->get()->map(function (Doctor $doctor) use ($appointments) {
            $own = $appointments->get($doctor->id, collect());
            $zavrseni = $own->where('status', 'zavrsen');

            $services = $zavrseni
                ->groupBy('service_id')
                ->map(function ($group) {
                    $service = $group->first()->service;

                    return [
                        'name' => $service?->name ?? '—',
                        'count' => $group->count(),
                        'price' => $service?->price_rsd ?? 0,
                        'total' => $group->count() * ($service?->price_rsd ?? 0),
                    ];
                })
                ->sortByDesc('total')
                ->values()
                ->all();

            return [
                'doctor' => $doctor,
                'services' => $services,
                'zavrseno' => $zavrseni->count(),
                'prihod' => collect($services)->sum('total'),
                'predstojeci' => $own->whereIn('status', ['zahtev', 'zakazan', 'potvrdjen'])->count(),
                'nije_dosao' => $own->where('status', 'nije_dosao')->count(),
                'otkazano' => $own->whereIn('status', ['otkazan', 'odbijen'])->count(),
            ];
        });

        return [
            'doctors' => $doctors->all(),
            'total' => [
                'zavrseno' => $doctors->sum('zavrseno'),
                'prihod' => $doctors->sum('prihod'),
                'nije_dosao' => $doctors->sum('nije_dosao'),
                'otkazano' => $doctors->sum('otkazano'),
            ],
            'month' => $month,
        ];
    }

    protected function getViewData(): array
    {
        $month = $this->getMonth();

        return static::buildReport($month) + [
            'monthLabel' => static::MONTH_NAMES[$month->month - 1] . ' ' . $month->year . '.',
            'printUrl' => route('stampa.izvestaj-rada', ['month' => $month->format('Y-m')]),
        ];
    }
}
