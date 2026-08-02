<?php

namespace App\Filament\Resources\Doctors\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DoctorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titula'),
                TextColumn::make('name')
                    ->label('Ime i prezime')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('specialty')
                    ->label('Specijalnost')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('appointments_count')
                    ->label('Termina')
                    ->counts('appointments'),
                TextColumn::make('ics_token')
                    ->label('Kalendar (ICS link)')
                    ->state(fn ($record) => $record->icsUrl())
                    ->limit(40)
                    ->copyable()
                    ->copyMessage('ICS link kopiran — doktor ga dodaje u svoj kalendar')
                    ->tooltip('Doktor ovaj link jednom doda u Google/Apple/Outlook kalendar i automatski vidi sve svoje termine.'),
                IconColumn::make('active')
                    ->label('Aktivan')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()->label('Izmeni'),
            ])
            ->defaultSort('name');
    }
}
