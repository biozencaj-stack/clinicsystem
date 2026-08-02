<?php

namespace App\Filament\Resources\Patients\RelationManagers;

use App\Models\WhatsappMessage;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WhatsappMessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'whatsappMessages';

    protected static ?string $title = 'WhatsApp poruke';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->modelLabel('poruka')
            ->pluralModelLabel('poruke')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Vreme')
                    ->dateTime('d.m.Y. H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Vrsta')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => WhatsappMessage::TYPES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'potvrda' => 'success',
                        'podsetnik' => 'info',
                        'nalaz' => 'warning',
                        'bot' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('body')
                    ->label('Poruka')
                    ->limit(90)
                    ->wrap()
                    ->tooltip(fn ($record) => $record->body),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => WhatsappMessage::STATUSES[$state] ?? $state),
            ])
            ->headerActions([])
            ->recordActions([])
            ->defaultSort('created_at', 'desc');
    }
}
