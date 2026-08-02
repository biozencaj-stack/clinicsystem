<?php

namespace App\Filament\Resources\Doctors\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DoctorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('title')
                    ->label('Titula')
                    ->options([
                        'dr' => 'dr',
                        'dr sci. med.' => 'dr sci. med.',
                        'prof. dr' => 'prof. dr',
                        'doc. dr' => 'doc. dr',
                        'spec. dr' => 'spec. dr',
                    ])
                    ->default('dr')
                    ->required(),
                TextInput::make('name')
                    ->label('Ime i prezime')
                    ->required(),
                TextInput::make('specialty')
                    ->label('Specijalnost')
                    ->placeholder('npr. radiolog, kardiolog…')
                    ->required(),
                TextInput::make('phone')
                    ->label('Telefon (WhatsApp za izmene rasporeda)')
                    ->tel(),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email(),
                ColorPicker::make('color')
                    ->label('Boja u kalendaru'),
                Toggle::make('active')
                    ->label('Aktivan')
                    ->default(true),
            ]);
    }
}
