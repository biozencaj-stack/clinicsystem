<?php

namespace App\Filament\Resources\KartonEntries\Pages;

use App\Filament\Resources\KartonEntries\KartonEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKartonEntries extends ListRecords
{
    protected static string $resource = KartonEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Novi unos'),
        ];
    }
}
