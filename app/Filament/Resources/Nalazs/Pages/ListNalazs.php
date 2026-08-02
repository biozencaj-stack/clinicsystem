<?php

namespace App\Filament\Resources\Nalazs\Pages;

use App\Filament\Resources\Nalazs\NalazResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNalazs extends ListRecords
{
    protected static string $resource = NalazResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Dodaj nalaz'),
        ];
    }
}
