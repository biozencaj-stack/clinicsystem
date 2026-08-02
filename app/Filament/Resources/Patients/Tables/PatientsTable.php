<?php

namespace App\Filament\Resources\Patients\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('notification_channel')
                    ->label('Obaveštenja')
                    ->badge()
                    ->state(fn ($record) => match (true) {
                        (bool) $record->whatsapp_opt_in => 'WhatsApp',
                        (bool) $record->viber_opt_in => 'Viber',
                        $record->email_opt_in && filled($record->email) => 'E-mail',
                        default => 'Bez saglasnosti',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'WhatsApp' => 'success',
                        'Viber' => 'viber',
                        'E-mail' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('appointments_count')
                    ->label('Poseta')
                    ->counts('appointments'),
                TextColumn::make('no_show_count')
                    ->label('Nedolasci')
                    ->state(fn ($record) => $record->appointments()->where('status', 'nije_dosao')->count())
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 3 => 'danger',
                        $state === 2 => 'warning',
                        default => 'gray',
                    })
                    ->tooltip('Broj termina na koje pacijent nije došao'),
                TextColumn::make('created_at')
                    ->label('Otvoren')
                    ->date('d.m.Y.')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('kanal')
                    ->label('Kanal obaveštenja')
                    ->options([
                        'whatsapp' => 'WhatsApp',
                        'viber' => 'Viber',
                        'email' => 'E-mail',
                        'nista' => 'Bez saglasnosti',
                    ])
                    ->query(fn ($query, array $data) => match ($data['value'] ?? null) {
                        'whatsapp' => $query->where('whatsapp_opt_in', true),
                        'viber' => $query->where('whatsapp_opt_in', false)->where('viber_opt_in', true),
                        'email' => $query->where('whatsapp_opt_in', false)->where('viber_opt_in', false)->where('email_opt_in', true),
                        'nista' => $query->where('whatsapp_opt_in', false)->where('viber_opt_in', false)->where('email_opt_in', false),
                        default => $query,
                    }),
                \Filament\Tables\Filters\SelectFilter::make('lecen_kod')
                    ->label('Lečen kod doktora')
                    ->options(fn () => \App\Models\Doctor::orderBy('name')->pluck('name', 'id')->all())
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas('appointments', fn ($q) => $q->where('doctor_id', $data['value']))
                        : $query),
                \Filament\Tables\Filters\SelectFilter::make('gender')
                    ->label('Pol')
                    ->options(['M' => 'Muški', 'Z' => 'Ženski']),
                \Filament\Tables\Filters\Filter::make('sa_nedolascima')
                    ->label('Sa nedolascima')
                    ->query(fn ($query) => $query->whereHas('appointments', fn ($q) => $q->where('status', 'nije_dosao'))),
                \Filament\Tables\Filters\Filter::make('neaktivni')
                    ->label('Bez posete 12+ meseci')
                    ->query(fn ($query) => $query->whereDoesntHave('appointments', fn ($q) => $q
                        ->whereIn('status', ['zavrsen', 'zakazan', 'potvrdjen'])
                        ->where('starts_at', '>=', now()->subYear()))),
            ])
            ->recordActions([
                EditAction::make()->label('Karton'),
            ])
            ->defaultSort('last_name');
    }
}
