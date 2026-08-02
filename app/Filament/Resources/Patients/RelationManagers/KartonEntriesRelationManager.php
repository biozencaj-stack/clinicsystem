<?php

namespace App\Filament\Resources\Patients\RelationManagers;

use App\Models\KartonEntry;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KartonEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'kartonEntries';

    protected static ?string $title = 'Karton';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('entry_date')
                    ->label('Datum')
                    ->default(now())
                    ->required(),
                Select::make('type')
                    ->label('Vrsta unosa')
                    ->options(KartonEntry::TYPES)
                    ->default('anamneza')
                    ->required(),
                Select::make('doctor_id')
                    ->label('Doktor')
                    ->relationship('doctor', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->visible(fn () => ! auth()->user()?->isDoctor())
                    ->preload(),
                TextInput::make('diagnosis_code')
                    ->label('MKB-10 šifra')
                    ->placeholder('npr. I10'),
                TextInput::make('title')
                    ->label('Naslov')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->label('Sadržaj')
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->modelLabel('unos u karton')
            ->pluralModelLabel('unosi u karton')
            ->columns([
                TextColumn::make('entry_date')
                    ->label('Datum')
                    ->date('d.m.Y.')
                    ->sortable(),
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
                    ->limit(50),
                TextColumn::make('doctor.name')
                    ->label('Doktor'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Novi unos')
                    ->modalHeading('Novi unos u karton')
                    ->mutateDataUsing(function (array $data) {
                        if (auth()->user()?->isDoctor()) {
                            $data['doctor_id'] = auth()->user()->doctor_id;
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                \App\Filament\Actions\PosaljiPacijentu::make('izveštaj', fn ($record) => $record->downloadUrl()),
                Action::make('stampa')
                    ->label('Štampaj')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('stampa.izvestaj', $record))
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->label('Izmeni')
                    ->modalHeading('Izmena unosa u kartonu')
                    ->visible(fn ($record) => ! auth()->user()?->isDoctor()
                        || $record->doctor_id === auth()->user()->doctor_id),
            ])
            ->defaultSort('entry_date', 'desc');
    }
}
