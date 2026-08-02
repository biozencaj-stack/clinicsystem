<?php

namespace App\Filament\Resources\Nalazs\Pages;

use App\Filament\Resources\Nalazs\NalazResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNalaz extends EditRecord
{
    protected static string $resource = NalazResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
