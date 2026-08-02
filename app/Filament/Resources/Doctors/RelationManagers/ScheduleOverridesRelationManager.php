<?php

namespace App\Filament\Resources\Doctors\RelationManagers;

use App\Models\Service;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScheduleOverridesRelationManager extends RelationManager
{
    protected static string $relationship = 'scheduleOverrides';

    protected static ?string $title = 'Posebni dani';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Datum')
                    ->required(),
                TextInput::make('reason')
                    ->label('Razlog')
                    ->placeholder('npr. subotnji rad, zamena smene'),
                Repeater::make('periods')
                    ->label('Radni periodi za taj dan')
                    ->schema([
                        TimePicker::make('starts_at')
                            ->label('Od')
                            ->seconds(false)
                            ->required(),
                        TimePicker::make('ends_at')
                            ->label('Do')
                            ->seconds(false)
                            ->required(),
                        Select::make('service_ids')
                            ->label('Samo za usluge (prazno = sve)')
                            ->multiple()
                            ->options(Service::where('active', true)->orderBy('name')->pluck('name', 'id')),
                    ])
                    ->columns(3)
                    ->minItems(1)
                    ->defaultItems(1)
                    ->addActionLabel('Dodaj period')
                    ->columnSpanFull()
                    ->helperText('Ovaj raspored za izabrani datum u potpunosti zamenjuje nedeljni raspored doktora.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('poseban dan')
            ->pluralModelLabel('posebni dani')
            ->columns([
                TextColumn::make('date')
                    ->label('Datum')
                    ->date('d.m.Y.')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Razlog')
                    ->placeholder('—'),
                TextColumn::make('periods')
                    ->label('Periodi')
                    ->state(fn ($record) => collect($record->periods ?? [])
                        ->map(fn ($p) => $p['starts_at'] . '–' . $p['ends_at'])
                        ->join(', ')),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Dodaj poseban dan')
                    ->modalHeading('Novi poseban dan'),
            ])
            ->recordActions([
                EditAction::make()->label('Izmeni')->modalHeading('Izmena posebnog dana'),
                DeleteAction::make()->label('Obriši')->modalHeading('Brisanje posebnog dana'),
            ])
            ->defaultSort('date', 'desc');
    }
}
