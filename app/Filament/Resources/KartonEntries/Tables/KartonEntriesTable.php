<?php

namespace App\Filament\Resources\KartonEntries\Tables;

use App\Models\KartonEntry;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KartonEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_date')
                    ->label('Datum')
                    ->date('d.m.Y.')
                    ->sortable(),
                TextColumn::make('patient.full_name')
                    ->label('Pacijent')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('type')
                    ->label('Vrsta')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => KartonEntry::TYPES[$state] ?? $state),
                TextColumn::make('diagnosis_code')
                    ->label('MKB-10')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('title')
                    ->label('Naslov')
                    ->limit(45)
                    ->searchable(),
                TextColumn::make('doctor.name')
                    ->label('Doktor'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Vrsta')
                    ->options(KartonEntry::TYPES),
                SelectFilter::make('doctor_id')
                    ->label('Doktor')
                    ->relationship('doctor', 'name'),
            ])
            ->recordActions([
                \App\Filament\Actions\PosaljiPacijentu::make('izveštaj', fn ($record) => $record->downloadUrl()),
                Action::make('stampa')
                    ->label('Štampaj izveštaj')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('stampa.izvestaj', $record))
                    ->openUrlInNewTab(),
                EditAction::make()->label('Izmeni'),
            ])
            ->defaultSort('entry_date', 'desc');
    }
}
