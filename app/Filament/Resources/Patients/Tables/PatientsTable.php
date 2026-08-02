<?php

namespace App\Filament\Resources\Patients\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('last_name')
                    ->label('Prezime')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label('Ime')
                    ->searchable(),
                TextColumn::make('date_of_birth')
                    ->label('Datum rođenja')
                    ->date('d.m.Y.')
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),
                IconColumn::make('whatsapp_opt_in')
                    ->label('WhatsApp')
                    ->boolean(),
                TextColumn::make('appointments_count')
                    ->label('Poseta')
                    ->counts('appointments'),
                TextColumn::make('created_at')
                    ->label('Otvoren')
                    ->date('d.m.Y.')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('whatsapp_opt_in')
                    ->label('WhatsApp saglasnost'),
            ])
            ->recordActions([
                EditAction::make()->label('Karton'),
            ])
            ->defaultSort('last_name');
    }
}
