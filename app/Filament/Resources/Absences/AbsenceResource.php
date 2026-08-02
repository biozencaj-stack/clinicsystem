<?php

namespace App\Filament\Resources\Absences;

use App\Filament\Resources\Absences\Pages\CreateAbsence;
use App\Filament\Resources\Absences\Pages\EditAbsence;
use App\Filament\Resources\Absences\Pages\ListAbsences;
use App\Models\Absence;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static string|UnitEnum|null $navigationGroup = 'Zakazivanje';

    protected static ?string $navigationLabel = 'Odsustva i praznici';

    protected static ?string $modelLabel = 'odsustvo';

    protected static ?string $pluralModelLabel = 'Odsustva i praznici';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('doctor_id')
                    ->label('Doktor')
                    ->relationship('doctor', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->placeholder('Cela klinika (praznik / neradni dan)')
                    ->preload(),
                TextInput::make('reason')
                    ->label('Razlog')
                    ->placeholder('npr. godišnji odmor, kongres, državni praznik')
                    ->required(),
                DatePicker::make('date_from')
                    ->label('Od datuma')
                    ->required(),
                DatePicker::make('date_to')
                    ->label('Do datuma')
                    ->afterOrEqual('date_from')
                    ->required(),
                Toggle::make('repeat_yearly')
                    ->label('Ponavlja se svake godine')
                    ->helperText('Za državne i verske praznike.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('doctor.name')
                    ->label('Doktor')
                    ->placeholder('Cela klinika')
                    ->badge()
                    ->color(fn ($record) => $record->doctor_id ? 'info' : 'warning'),
                TextColumn::make('reason')
                    ->label('Razlog')
                    ->searchable(),
                TextColumn::make('date_from')
                    ->label('Od')
                    ->date('d.m.Y.')
                    ->sortable(),
                TextColumn::make('date_to')
                    ->label('Do')
                    ->date('d.m.Y.'),
                IconColumn::make('repeat_yearly')
                    ->label('Godišnje')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('doctor_id')
                    ->label('Doktor')
                    ->relationship('doctor', 'name'),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()->label('Izmeni'),
                \Filament\Actions\DeleteAction::make()->label('Obriši')->modalHeading('Brisanje odsustva'),
            ])
            ->defaultSort('date_from', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAbsences::route('/'),
            'create' => CreateAbsence::route('/create'),
            'edit' => EditAbsence::route('/{record}/edit'),
        ];
    }
}
