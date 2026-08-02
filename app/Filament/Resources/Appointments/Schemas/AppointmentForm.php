<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Models\Service;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('patient_id')
                    ->label('Pacijent')
                    ->relationship('patient', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->full_name} ({$record->phone})")
                    ->searchable(['first_name', 'last_name', 'phone'])
                    ->preload()
                    ->required(),
                Select::make('doctor_id')
                    ->label('Doktor')
                    ->relationship('doctor', 'name', fn ($query) => $query->where('active', true))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->full_name} — {$record->specialty}")
                    ->preload()
                    ->required(),
                Select::make('service_id')
                    ->label('Usluga')
                    ->relationship('service', 'name', fn ($query) => $query->where('active', true))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->duration_minutes} min)")
                    ->searchable(['name'])
                    ->preload()
                    ->live()
                    ->required(),
                DateTimePicker::make('starts_at')
                    ->label('Datum i vreme')
                    ->seconds(false)
                    ->minutesStep(15)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        if ($state && $get('service_id')) {
                            $service = Service::find($get('service_id'));
                            if ($service) {
                                $set('ends_at', \Illuminate\Support\Carbon::parse($state)->addMinutes($service->duration_minutes)->toDateTimeString());
                            }
                        }
                    })
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->label('Kraj termina')
                    ->seconds(false)
                    ->helperText('Popunjava se automatski iz trajanja usluge.'),
                Select::make('status')
                    ->label('Status')
                    ->options(Appointment::STATUSES)
                    ->default('zakazan')
                    ->required(),
                Select::make('source')
                    ->label('Izvor zakazivanja')
                    ->options(Appointment::SOURCES)
                    ->default('recepcija')
                    ->required(),
                Textarea::make('note')
                    ->label('Napomena')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }
}
