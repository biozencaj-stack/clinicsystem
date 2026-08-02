<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Naziv usluge')
                    ->required(),
                Select::make('category')
                    ->label('Oblast')
                    ->options([
                        'Radiologija' => 'Radiologija',
                        'Kardiologija' => 'Kardiologija',
                        'Gastroenterologija' => 'Gastroenterologija',
                        'Endokrinologija' => 'Endokrinologija',
                        'Reumatologija' => 'Reumatologija',
                        'Neurologija' => 'Neurologija',
                        'Urologija' => 'Urologija',
                        'Ostalo' => 'Ostalo',
                    ])
                    ->required(),
                TextInput::make('duration_minutes')
                    ->label('Trajanje (min)')
                    ->numeric()
                    ->default(30)
                    ->required(),
                TextInput::make('buffer_before')
                    ->label('Buffer pre (min)')
                    ->helperText('Priprema sobe/aparata — pacijent ga ne vidi, ali blokira raspored.')
                    ->numeric()
                    ->default(0),
                TextInput::make('buffer_after')
                    ->label('Buffer posle (min)')
                    ->helperText('npr. sterilizacija posle intervencije.')
                    ->numeric()
                    ->default(0),
                TextInput::make('price_rsd')
                    ->label('Cena (RSD)')
                    ->numeric(),
                Textarea::make('preparation')
                    ->label('Priprema za pregled')
                    ->helperText('Ovaj tekst se automatski šalje pacijentu u WhatsApp podsetniku.')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('active')
                    ->label('Aktivna')
                    ->default(true),
            ]);
    }
}
