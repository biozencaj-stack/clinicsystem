<?php

namespace App\Filament\Resources\Nalazs\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NalazsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('issued_at')
                    ->label('Datum')
                    ->date('d.m.Y.')
                    ->sortable(),
                TextColumn::make('patient.full_name')
                    ->label('Pacijent')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('title')
                    ->label('Naziv')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('doctor.name')
                    ->label('Doktor'),
                IconColumn::make('ready_notified_at')
                    ->label('Obavešten')
                    ->boolean()
                    ->state(fn ($record) => filled($record->ready_notified_at))
                    ->tooltip('Da li je pacijentu poslata WhatsApp poruka da je nalaz spreman'),
            ])
            ->recordActions([
                \App\Filament\Actions\PosaljiPacijentu::make('nalaz', fn ($record) => $record->downloadUrl(), 'nalaz'),
                Action::make('stampa')
                    ->label('Štampaj')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('stampa.nalaz', $record))
                    ->openUrlInNewTab(),
                Action::make('link')
                    ->label('Link')
                    ->icon('heroicon-o-link')
                    ->url(fn ($record) => $record->downloadUrl())
                    ->openUrlInNewTab(),
                EditAction::make()->label('Izmeni'),
            ])
            ->defaultSort('issued_at', 'desc');
    }
}
