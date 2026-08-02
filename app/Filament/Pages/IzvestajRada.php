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
    protected string $view = 'filament.pages.izvestaj-rada';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Zakazivanje';

    protected static ?string $navigationLabel = 'Izveštaj rada';

    protected static ?string $title = 'Izveštaj rada';

    protected static ?int $navigationSort = 5;

    public string $dateFrom = '';

    public string $dateTo = '';

    public static function canAccess(): bool
    {
        return ! auth()->user()?->isDoctor();
    }

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->endOfMonth()->toDateString();
    }

    public function thisMonth(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->endOfMonth()->toDateString();
    }

    public function lastMonth(): void
    {
        $this->dateFrom = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $this->dateTo = now()->subMonthNoOverflow()->endOfMonth()->toDateString();
    }

    public function last30(): void
    {
        $this->dateFrom = now()->subDays(30)->toDateString();
        $this->dateTo = now()->toDateString();
    }

    /**
     * Pregled rada za period: po doktoru — koje usluge, koliko puta,
     * plus nedolasci i otkazivanja. Bez finansija.
     */
    public static function buildReport(Carbon $from, Carbon $to): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();

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
                ->map(fn ($group) => [
                    'name' => $group->first()->service?->name ?? '—',
                    'count' => $group->count(),
                ])
                ->sortByDesc('count')
                ->values()
                ->all();

            return [
                'doctor' => $doctor,
                'services' => $services,
                'zavrseno' => $zavrseni->count(),
                'predstojeci' => $own->whereIn('status', ['zahtev', 'zakazan', 'potvrdjen'])->count(),
                'nije_dosao' => $own->where('status', 'nije_dosao')->count(),
                'otkazano' => $own->whereIn('status', ['otkazan', 'odbijen'])->count(),
            ];
        });

        return [
            'doctors' => $doctors->all(),
            'total' => [
                'zavrseno' => $doctors->sum('zavrseno'),
                'predstojeci' => $doctors->sum('predstojeci'),
                'nije_dosao' => $doctors->sum('nije_dosao'),
                'otkazano' => $doctors->sum('otkazano'),
            ],
        ];
    }

    protected function getViewData(): array
    {
        $from = Carbon::parse($this->dateFrom ?: now()->startOfMonth());
        $to = Carbon::parse($this->dateTo ?: now()->endOfMonth());

        if ($to->lt($from)) {
            $to = $from->copy();
        }

        return static::buildReport($from, $to) + [
            'rangeLabel' => $from->format('d.m.Y.') . ' — ' . $to->format('d.m.Y.'),
            'printUrl' => route('stampa.izvestaj-rada', [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ]),
        ];
    }
}
