<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label('Ime')
                    ->required(),
                TextInput::make('last_name')
                    ->label('Prezime')
                    ->required(),
                TextInput::make('jmbg')
                    ->label('JMBG')
                    ->length(13)
                    ->numeric(),
                DatePicker::make('date_of_birth')
                    ->label('Datum rođenja'),
                Select::make('gender')
                    ->label('Pol')
                    ->options(['M' => 'Muški', 'Z' => 'Ženski']),
                TextInput::make('phone')
                    ->label('Telefon (WhatsApp)')
                    ->tel()
                    ->placeholder('+3816…')
                    ->required(),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email(),
                TextInput::make('address')
                    ->label('Adresa'),
                Toggle::make('whatsapp_opt_in')
                    ->label('Saglasnost za WhatsApp obaveštenja')
                    ->helperText('Sistem bira kanal po prioritetu: WhatsApp → Viber → e-mail.')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('whatsapp_opt_in_at', $state ? now() : null);
                    }),
                Toggle::make('viber_opt_in')
                    ->label('Saglasnost za Viber obaveštenja'),
                Toggle::make('email_opt_in')
                    ->label('Saglasnost za e-mail obaveštenja'),
                Textarea::make('note')
                    ->label('Napomena')
                    ->columnSpanFull(),
            ]);
    }
}
