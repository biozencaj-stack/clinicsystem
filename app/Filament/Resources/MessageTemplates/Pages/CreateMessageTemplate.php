<?php

namespace App\Filament\Resources\MessageTemplates\Pages;

use App\Filament\Resources\MessageTemplates\MessageTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMessageTemplate extends CreateRecord
{
    protected static string $resource = MessageTemplateResource::class;

    protected static ?string $title = 'Novi šablon poruke';
}
