<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditPatient extends EditRecord
{
    protected static string $resource = PatientResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Karton — ' . $this->getRecord()->full_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Obriši pacijenta')
                ->modalHeading('Brisanje pacijenta'),
        ];
    }
}
