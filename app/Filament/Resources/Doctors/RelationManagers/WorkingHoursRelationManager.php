<?php

namespace App\Filament\Resources\Doctors\RelationManagers;

use App\Models\DoctorWorkingHour;
use App\Models\Service;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkingHoursRelationManager extends RelationManager
{
    protected static string $relationship = 'workingHours';

    protected static ?string $title = 'Radno vreme';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('weekday')
                    ->label('Dan u nedelji')
                    ->options(DoctorWorkingHour::WEEKDAYS)
                    ->required(),
                TimePicker::make('starts_at')
                    ->label('Od')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('ends_at')
                    ->label('Do')
                    ->seconds(false)
                    ->after('starts_at')
                    ->required(),
                Select::make('service_ids')
                    ->label('Samo za usluge (prazno = sve usluge)')
                    ->multiple()
                    ->options(Service::where('active', true)->orderBy('name')->pluck('name', 'id'))
                    ->placeholder('Sve usluge')
                    ->helperText('npr. ultrazvuk samo pre podne — izaberi usluge za ovaj period.')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('period')
            ->pluralModelLabel('periodi')
            ->columns([
                TextColumn::make('weekday')
                    ->label('Dan')
                    ->formatStateUsing(fn ($state) => DoctorWorkingHour::WEEKDAYS[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Od'),
                TextColumn::make('ends_at')
                    ->label('Do'),
                TextColumn::make('service_ids')
                    ->label('Usluge')
                    ->state(function ($record) {
                        if (blank($record->service_ids)) {
                            return 'Sve usluge';
                        }

                        return Service::whereIn('id', $record->service_ids)->pluck('name')->join(', ');
                    })
                    ->limit(60)
                    ->badge()
                    ->color(fn ($record) => blank($record->service_ids) ? 'gray' : 'info'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Dodaj period')
                    ->modalHeading('Novi radni period'),
            ])
            ->recordActions([
                EditAction::make()->label('Izmeni')->modalHeading('Izmena radnog perioda'),
                DeleteAction::make()->label('Obriši')->modalHeading('Brisanje radnog perioda'),
            ])
            ->defaultSort('weekday')
            ->emptyStateHeading('Radno vreme nije uneto')
            ->emptyStateDescription('Bez radnog vremena doktor nema slobodne termine u slot engine-u.');
    }
}
