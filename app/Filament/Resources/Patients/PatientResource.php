<?php

namespace App\Filament\Resources\Patients;

use App\Filament\Resources\Patients\Pages\CreatePatient;
use App\Filament\Resources\Patients\Pages\EditPatient;
use App\Filament\Resources\Patients\Pages\ListPatients;
use App\Filament\Resources\Patients\RelationManagers\KartonEntriesRelationManager;
use App\Filament\Resources\Patients\RelationManagers\NalaziRelationManager;
use App\Filament\Resources\Patients\RelationManagers\MessagesRelationManager;
use App\Filament\Resources\Patients\Schemas\PatientForm;
use App\Filament\Resources\Patients\Tables\PatientsTable;
use App\Models\Patient;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Pacijenti';

    protected static ?string $navigationLabel = 'Pacijenti';

    protected static ?string $modelLabel = 'pacijent';

    protected static ?string $pluralModelLabel = 'Pacijenti';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'phone', 'jmbg', 'email'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->full_name;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return array_filter([
            'Telefon' => $record->phone,
            'Rođen/a' => $record->date_of_birth?->format('d.m.Y.'),
        ]);
    }

    /** Doktor vidi samo pacijente koje je lečio (termin, unos u karton ili nalaz). */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->isDoctor()) {
            $doctorId = $user->doctor_id;
            $query->where(function ($q) use ($doctorId) {
                $q->whereHas('appointments', fn ($qq) => $qq->where('doctor_id', $doctorId))
                    ->orWhereHas('kartonEntries', fn ($qq) => $qq->where('doctor_id', $doctorId))
                    ->orWhereHas('nalazi', fn ($qq) => $qq->where('doctor_id', $doctorId));
            });
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return ! auth()->user()?->isDoctor();
    }

    public static function canDelete($record): bool
    {
        return ! auth()->user()?->isDoctor();
    }

    public static function form(Schema $schema): Schema
    {
        // Doktor otvara karton, ali administrativne podatke menja samo recepcija.
        return PatientForm::configure($schema)
            ->disabled(fn () => (bool) auth()->user()?->isDoctor());
    }

    public static function table(Table $table): Table
    {
        return PatientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            KartonEntriesRelationManager::class,
            NalaziRelationManager::class,
            MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPatients::route('/'),
            'create' => CreatePatient::route('/create'),
            'edit' => EditPatient::route('/{record}/edit'),
        ];
    }
}
