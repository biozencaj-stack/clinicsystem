<?php

namespace App\Filament\Resources\WhatsappMessages\Schemas;

use App\Models\WhatsappMessage;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WhatsappMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('patient_id')
                    ->label('Pacijent')
                    ->relationship('patient', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->disabled(),
                Select::make('type')
                    ->label('Vrsta')
                    ->options(WhatsappMessage::TYPES)
                    ->disabled(),
                TextInput::make('to_phone')
                    ->label('Broj')
                    ->disabled(),
                Select::make('status')
                    ->label('Status')
                    ->options(WhatsappMessage::STATUSES),
                DateTimePicker::make('scheduled_for')
                    ->label('Zakazano za')
                    ->seconds(false),
                Textarea::make('body')
                    ->label('Poruka')
                    ->rows(5)
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}
