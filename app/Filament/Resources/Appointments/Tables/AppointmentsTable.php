<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Termin')
                    ->dateTime('d.m.Y. H:i')
                    ->sortable(),
                TextColumn::make('patient.full_name')
                    ->label('Pacijent')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('doctor.name')
                    ->label('Doktor')
                    ->sortable(),
                TextColumn::make('service.name')
                    ->label('Usluga')
                    ->limit(30),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Appointment::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'zahtev' => 'warning',
                        'zakazan' => 'info',
                        'potvrdjen' => 'success',
                        'zavrsen' => 'gray',
                        'otkazan', 'odbijen', 'nije_dosao' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('source')
                    ->label('Izvor')
                    ->formatStateUsing(fn (string $state) => Appointment::SOURCES[$state] ?? $state)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Appointment::STATUSES),
                SelectFilter::make('doctor_id')
                    ->label('Doktor')
                    ->relationship('doctor', 'name'),
            ])
            ->recordActions([
                Action::make('potvrdi')
                    ->label('Potvrdi zahtev')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'zahtev' && ! auth()->user()?->isDoctor())
                    ->requiresConfirmation()
                    ->modalHeading('Potvrda zahteva za termin')
                    ->modalDescription('Potvrdom se pacijentu automatski šalje potvrda njegovim kanalom i zakazuje podsetnik 24h pre termina.')
                    ->action(fn ($record) => $record->update(['status' => 'zakazan'])),
                Action::make('potvrdi_dolazak')
                    ->label('Potvrdi dolazak')
                    ->icon('heroicon-o-hand-thumb-up')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'zakazan' && ! auth()->user()?->isDoctor())
                    ->tooltip('Pacijent je potvrdio telefonom — jedan klik i termin je potvrđen.')
                    ->action(function ($record) {
                        $record->update(['status' => 'potvrdjen']);
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Dolazak potvrđen')
                            ->body($record->patient?->full_name . ' — ' . $record->starts_at->format('d.m.Y. u H:i'))
                            ->send();
                    }),
                Action::make('odbij')
                    ->label('Odbij')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'zahtev' && ! auth()->user()?->isDoctor())
                    ->requiresConfirmation()
                    ->modalHeading('Odbijanje zahteva')
                    ->modalDescription('Pacijent automatski dobija poruku da termin nije dostupan, sa pozivom da se javi za drugi termin.')
                    ->action(function ($record) {
                        $record->update(['status' => 'odbijen']);
                        \App\Models\Message::sendRejection($record);
                    }),
                EditAction::make()->label('Izmeni'),
            ])
            ->defaultSort('starts_at', 'desc');
    }
}
