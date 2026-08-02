<?php

namespace App\Filament\Resources\KartonEntries\Pages;

use App\Filament\Resources\KartonEntries\KartonEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKartonEntry extends CreateRecord
{
    protected static string $resource = KartonEntryResource::class;

    protected static ?string $title = 'Novi unos u karton';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()?->isDoctor()) {
            $data['doctor_id'] = auth()->user()->doctor_id;
        }

        return $data;
    }
}
