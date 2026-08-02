<?php

namespace App\Filament\Resources\Absences\Pages;

use App\Filament\Resources\Absences\AbsenceResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAbsence extends CreateRecord
{
    protected static string $resource = AbsenceResource::class;

    protected static ?string $title = 'Novo odsustvo';

    /** Doktorski nalog uvek unosi odsustvo za sebe. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()?->isDoctor()) {
            $data['doctor_id'] = auth()->user()->doctor_id;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $affected = $this->record->affectedAppointments()->get();

        if ($affected->isEmpty()) {
            return;
        }

        Notification::make()
            ->warning()
            ->persistent()
            ->title('Pažnja: ' . $affected->count() . ' zakazanih termina u ovom periodu')
            ->body(
                $affected->take(5)->map(fn ($a) => $a->starts_at->format('d.m. H:i') . ' — ' . $a->patient?->full_name . ' (' . $a->service?->name . ')')->join("\n")
                . ($affected->count() > 5 ? "\n… i još " . ($affected->count() - 5) : '')
                . "\nPomerite ih i obavestite pacijente iz liste termina."
            )
            ->send();
    }
}
