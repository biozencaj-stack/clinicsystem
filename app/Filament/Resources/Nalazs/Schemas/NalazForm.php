<?php

namespace App\Filament\Resources\Nalazs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NalazForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('patient_id')
                    ->label('Pacijent')
                    ->relationship('patient', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->full_name} ({$record->phone})")
                    ->searchable(['first_name', 'last_name'])
                    ->preload()
                    ->required(),
                Select::make('doctor_id')
                    ->label('Doktor')
                    ->relationship('doctor', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->visible(fn () => ! auth()->user()?->isDoctor())
                    ->preload(),
                TextInput::make('title')
                    ->label('Naziv nalaza')
                    ->placeholder('npr. MR lumbalne kičme')
                    ->required(),
                DatePicker::make('issued_at')
                    ->label('Datum izdavanja')
                    ->default(now())
                    ->required(),
                Textarea::make('content')
                    ->label('Sadržaj nalaza')
                    ->helperText('Kada je sadržaj unet ovde, dugme „Štampaj“ generiše brendiran PDF sa logom klinike i potpisom doktora.')
                    ->rows(10)
                    ->columnSpanFull(),
                FileUpload::make('file_path')
                    ->label('PDF dokument')
                    ->disk('public')
                    ->directory('nalazi')
                    ->acceptedFileTypes(['application/pdf'])
                    ->helperText('Čuvanjem nalaza pacijent sa WhatsApp saglasnošću automatski dobija poruku sa bezbednim linkom (u demo režimu poruka se samo beleži).')
                    ->columnSpanFull(),
            ]);
    }
}
