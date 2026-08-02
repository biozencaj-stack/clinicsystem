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
