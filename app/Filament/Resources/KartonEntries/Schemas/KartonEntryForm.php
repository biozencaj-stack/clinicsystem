<?php

namespace App\Filament\Resources\KartonEntries\Schemas;

use App\Models\KartonEntry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KartonEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('patient_id')
                    ->label('Pacijent')
                    ->relationship('patient', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->preload()
                    ->required(),
                Select::make('doctor_id')
                    ->label('Doktor')
                    ->relationship('doctor', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->preload(),
                DatePicker::make('entry_date')
                    ->label('Datum')
                    ->default(now())
                    ->required(),
                Select::make('type')
                    ->label('Vrsta unosa')
                    ->options(KartonEntry::TYPES)
                    ->default('anamneza')
                    ->required(),
                TextInput::make('diagnosis_code')
                    ->label('MKB-10 šifra')
                    ->placeholder('npr. I10'),
                TextInput::make('title')
                    ->label('Naslov')
                    ->required(),
                Textarea::make('content')
                    ->label('Sadržaj')
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }
}
