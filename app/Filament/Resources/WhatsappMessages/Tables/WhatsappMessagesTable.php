<?php

namespace App\Filament\Resources\WhatsappMessages\Tables;

use App\Models\WhatsappMessage;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WhatsappMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Vreme')
                    ->dateTime('d.m.Y. H:i')
                    ->sortable(),
                TextColumn::make('direction')
                    ->label('Smer')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'out' ? 'Odlazna' : 'Dolazna')
                    ->color(fn (string $state) => $state === 'out' ? 'info' : 'success'),
                TextColumn::make('patient.full_name')
                    ->label('Pacijent')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('type')
                    ->label('Vrsta')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => WhatsappMessage::TYPES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'potvrda' => 'success',
                        'podsetnik' => 'info',
                        'nalaz' => 'warning',
                        'izmena' => 'danger',
                        'bot' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('body')
                    ->label('Poruka')
                    ->limit(70)
                    ->wrap()
                    ->tooltip(fn ($record) => $record->body),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => WhatsappMessage::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'simulirano' => 'gray',
                        'zakazano' => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('scheduled_for')
                    ->label('Zakazano za')
                    ->dateTime('d.m.Y. H:i')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Vrsta')
                    ->options(WhatsappMessage::TYPES),
            ])
            ->recordActions([
                EditAction::make()->label('Detalji'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
