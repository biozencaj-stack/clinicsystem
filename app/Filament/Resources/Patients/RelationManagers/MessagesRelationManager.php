<?php

namespace App\Filament\Resources\Patients\RelationManagers;

use App\Models\Message;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Poruke';

    /** Komunikacija sa pacijentom je posao recepcije — doktor je ne vidi. */
    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return ! auth()->user()?->isDoctor();
    }

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
                TextColumn::make('channel')
                    ->label('Kanal')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Message::CHANNELS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'whatsapp' => 'success',
                        'viber' => 'viber',
                        'email' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('type')
                    ->label('Vrsta')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Message::TYPES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'potvrda' => 'success',
                        'podsetnik' => 'info',
                        'nalaz' => 'warning',
                        'bot' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('body')
                    ->label('Poruka')
                    ->limit(80)
                    ->wrap()
                    ->tooltip(fn ($record) => $record->body),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Message::STATUSES[$state] ?? $state),
            ])
            ->headerActions([])
            ->recordActions([])
            ->defaultSort('created_at', 'desc');
    }
}
