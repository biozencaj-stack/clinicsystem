<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Oblast')
                    ->badge()
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->label('Trajanje')
                    ->suffix(' min'),
                TextColumn::make('price_rsd')
                    ->label('Cena')
                    ->numeric(thousandsSeparator: '.')
                    ->suffix(' RSD')
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Aktivna')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Oblast')
                    ->options([
                        'Radiologija' => 'Radiologija',
                        'Kardiologija' => 'Kardiologija',
                        'Gastroenterologija' => 'Gastroenterologija',
                        'Endokrinologija' => 'Endokrinologija',
                        'Reumatologija' => 'Reumatologija',
                        'Neurologija' => 'Neurologija',
                        'Urologija' => 'Urologija',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Izmeni'),
            ])
            ->defaultSort('category');
    }
}
