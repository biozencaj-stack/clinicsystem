<?php

namespace App\Filament\Resources\Nalazs\Pages;

use App\Filament\Resources\Nalazs\NalazResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNalaz extends CreateRecord
{
    protected static string $resource = NalazResource::class;

    protected static ?string $title = 'Novi nalaz';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()?->isDoctor()) {
            $data['doctor_id'] = auth()->user()->doctor_id;
        }

        return $data;
    }
}
