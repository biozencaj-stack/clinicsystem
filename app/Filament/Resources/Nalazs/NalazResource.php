<?php

namespace App\Filament\Resources\Nalazs;

use App\Filament\Resources\Nalazs\Pages\CreateNalaz;
use App\Filament\Resources\Nalazs\Pages\EditNalaz;
use App\Filament\Resources\Nalazs\Pages\ListNalazs;
use App\Filament\Resources\Nalazs\Schemas\NalazForm;
use App\Filament\Resources\Nalazs\Tables\NalazsTable;
use App\Models\Nalaz;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NalazResource extends Resource
{
    protected static ?string $model = Nalaz::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Pacijenti';

    protected static ?string $navigationLabel = 'Nalazi';

    protected static ?string $modelLabel = 'nalaz';

    protected static ?string $pluralModelLabel = 'Nalazi';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    /** Doktor u globalnoj listi vidi nalaze koje je sam izdao. */
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
        return NalazForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NalazsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNalazs::route('/'),
            'create' => CreateNalaz::route('/create'),
            'edit' => EditNalaz::route('/{record}/edit'),
        ];
    }
}
