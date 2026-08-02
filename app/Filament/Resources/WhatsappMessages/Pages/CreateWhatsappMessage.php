<?php

namespace App\Filament\Resources\WhatsappMessages\Pages;

use App\Filament\Resources\WhatsappMessages\WhatsappMessageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWhatsappMessage extends CreateRecord
{
    protected static string $resource = WhatsappMessageResource::class;
}
