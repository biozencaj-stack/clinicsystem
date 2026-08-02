<?php

namespace App\Filament\Resources\KartonEntries;

use App\Filament\Resources\KartonEntries\Pages\CreateKartonEntry;
use App\Filament\Resources\KartonEntries\Pages\EditKartonEntry;
use App\Filament\Resources\KartonEntries\Pages\ListKartonEntries;
use App\Filament\Resources\KartonEntries\Schemas\KartonEntryForm;
use App\Filament\Resources\KartonEntries\Tables\KartonEntriesTable;
use App\Models\KartonEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KartonEntryResource extends Resource
{
    protected static ?string $model = KartonEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Pacijenti';

    protected static ?string $navigationLabel = 'Kartoni (svi unosi)';

    protected static ?string $modelLabel = 'unos u karton';

    protected static ?string $pluralModelLabel = 'Unosi u karton';

    protected static ?int $navigationSort = 2;

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'diagnosis_code', 'patient.first_name', 'patient.last_name'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->title;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return array_filter([
            'Pacijent' => $record->patient?->full_name,
            'Datum' => $record->entry_date?->format('d.m.Y.'),
            'MKB-10' => $record->diagnosis_code,
        ]);
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['patient']);
    }

    /** Doktor u globalnoj listi vidi unose koje je sam napisao. */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->isDoctor()) {
            $query->where('doctor_id', auth()->user()->doctor_id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return KartonEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KartonEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKartonEntries::route('/'),
            'create' => CreateKartonEntry::route('/create'),
            'edit' => EditKartonEntry::route('/{record}/edit'),
        ];
    }
}
