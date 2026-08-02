<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Models\Doctor;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use UnitEnum;

class Kalendar extends Page
{
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

        $appointments = Appointment::query()
            ->with(['patient', 'doctor', 'service'])
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->when($this->doctorId, fn ($q) => $q->where('doctor_id', $this->doctorId))
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Appointment $a) => $a->starts_at->toDateString());

        $days = collect(range(0, 6))->map(function (int $i) use ($weekStart, $appointments) {
            $date = $weekStart->copy()->addDays($i);

            return [
                'date' => $date,
                'isToday' => $date->isToday(),
                'appointments' => $appointments->get($date->toDateString(), collect()),
            ];
        });

        return [
            'days' => $days,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'doctors' => Doctor::where('active', true)->orderBy('name')->get(),
            'statusLabels' => Appointment::STATUSES,
        ];
    }
}
