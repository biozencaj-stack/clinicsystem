<?php

namespace App\Filament\Resources\Patients\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NalaziRelationManager extends RelationManager
{
    protected static string $relationship = 'nalazi';

    protected static ?string $title = 'Nalazi';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Naziv nalaza')
                    ->placeholder('npr. MR lumbalne kičme')
                    ->required(),
                Select::make('doctor_id')
                    ->label('Doktor')
                    ->relationship('doctor', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->preload(),
                DatePicker::make('issued_at')
                    ->label('Datum izdavanja')
                    ->default(now())
                    ->required(),
                FileUpload::make('file_path')
                    ->label('PDF dokument')
                    ->disk('public')
                    ->directory('nalazi')
                    ->acceptedFileTypes(['application/pdf'])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->modelLabel('nalaz')
            ->pluralModelLabel('nalazi')
            ->columns([
                TextColumn::make('issued_at')
                    ->label('Datum')
                    ->date('d.m.Y.')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Naziv'),
                TextColumn::make('doctor.name')
                    ->label('Doktor'),
                IconColumn::make('ready_notified_at')
                    ->label('Pacijent obavešten')
                    ->boolean()
                    ->state(fn ($record) => filled($record->ready_notified_at)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Novi nalaz')
                    ->modalHeading('Novi nalaz')
                    ->modalDescription('Čuvanjem nalaza pacijent automatski dobija WhatsApp poruku sa bezbednim linkom za preuzimanje (u demo režimu poruka se samo beleži).'),
            ])
            ->recordActions([
                Action::make('link')
                    ->label('Link za preuzimanje')
                    ->icon('heroicon-o-link')
                    ->url(fn ($record) => $record->downloadUrl())
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('issued_at', 'desc');
    }
}
