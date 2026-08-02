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
                TextColumn::make('zavrseno_mesec')
                    ->label('Završeno (mesec)')
                    ->state(fn ($record) => $record->appointments()
                        ->where('status', 'zavrsen')
                        ->whereBetween('starts_at', [now()->startOfMonth(), now()->endOfMonth()])
                        ->count())
                    ->badge()
                    ->color('success')
                    ->tooltip('Završeni pregledi u tekućem mesecu'),
                TextColumn::make('prihod_mesec')
                    ->label('Prihod (mesec)')
                    ->state(fn ($record) => number_format(
                        $record->appointments()
                            ->where('status', 'zavrsen')
                            ->whereBetween('starts_at', [now()->startOfMonth(), now()->endOfMonth()])
                            ->join('services', 'services.id', '=', 'appointments.service_id')
                            ->sum('services.price_rsd'),
                        0, ',', '.'
                    ) . ' RSD')
                    ->tooltip('Zbir cena završenih usluga u tekućem mesecu'),
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
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('specialty')
                    ->label('Specijalnost')
                    ->options(fn () => \App\Models\Doctor::query()
                        ->distinct()->orderBy('specialty')->pluck('specialty', 'specialty')->all()),
                \Filament\Tables\Filters\TernaryFilter::make('active')
                    ->label('Aktivan'),
            ])
            ->recordActions([
                EditAction::make()->label('Izmeni'),
            ])
            ->defaultSort('name');
    }
}
