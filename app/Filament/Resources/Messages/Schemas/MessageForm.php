<?php

namespace App\Filament\Resources\Messages\Schemas;

use App\Models\Message;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MessageForm
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
                Select::make('channel')
                    ->label('Kanal')
                    ->options(Message::CHANNELS)
                    ->disabled(),
                Select::make('type')
                    ->label('Vrsta')
                    ->options(Message::TYPES)
                    ->disabled(),
                TextInput::make('destination')
                    ->label('Primalac (broj / e-mail)')
                    ->disabled(),
                Select::make('status')
                    ->label('Status')
                    ->options(Message::STATUSES),
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
