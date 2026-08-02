<?php

namespace App\Filament\Resources\KartonEntries\Pages;

use App\Filament\Resources\KartonEntries\KartonEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKartonEntry extends EditRecord
{
    protected static string $resource = KartonEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
