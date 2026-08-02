<?php

namespace App\Filament\Resources\MessageTemplates;

use App\Filament\Resources\MessageTemplates\Pages\CreateMessageTemplate;
use App\Filament\Resources\MessageTemplates\Pages\EditMessageTemplate;
use App\Filament\Resources\MessageTemplates\Pages\ListMessageTemplates;
use App\Models\MessageTemplate;
use App\Models\Service;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

class MessageTemplateResource extends Resource
{
    protected static ?string $model = MessageTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|UnitEnum|null $navigationGroup = 'Komunikacija';

    protected static ?string $navigationLabel = 'Šabloni poruka';

    protected static ?string $modelLabel = 'šablon';

    protected static ?string $pluralModelLabel = 'Šabloni poruka';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return ! auth()->user()?->isDoctor();
    }

    public static function form(Schema $schema): Schema
    {
        $placeholders = collect(MessageTemplate::PLACEHOLDERS)
            ->map(fn ($desc, $key) => "{$key} — {$desc}")
            ->join(' · ');

        return $schema
            ->components([
                Select::make('event')
                    ->label('Događaj')
                    ->options(MessageTemplate::EVENTS)
                    ->live()
                    ->required(),
                TextInput::make('name')
                    ->label('Naziv šablona')
                    ->placeholder('npr. Podsetnik za gastroskopiju sa dijetom')
                    ->required(),
                Select::make('service_ids')
                    ->label('Samo za usluge (prazno = sve usluge)')
                    ->multiple()
                    ->options(Service::orderBy('name')->pluck('name', 'id'))
                    ->helperText('Šablon vezan za uslugu ima prednost nad opštim šablonom.'),
                TextInput::make('offset_hours')
                    ->label('Koliko sati pre termina')
                    ->numeric()
                    ->visible(fn ($get) => $get('event') === 'podsetnik')
                    ->placeholder('24'),
                Textarea::make('body')
                    ->label('Tekst poruke')
                    ->rows(6)
                    ->required()
                    ->helperText('Dostupni placeholder-i: ' . $placeholders)
                    ->columnSpanFull(),
                Toggle::make('active')
                    ->label('Aktivan')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event')
                    ->label('Događaj')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => MessageTemplate::EVENTS[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Naziv')
                    ->searchable(),
                TextColumn::make('service_ids')
                    ->label('Usluge')
                    ->state(function ($record) {
                        if (blank($record->service_ids)) {
                            return 'Sve usluge';
                        }

                        return Service::whereIn('id', $record->service_ids)->pluck('name')->join(', ');
                    })
                    ->limit(40)
                    ->badge()
                    ->color(fn ($record) => blank($record->service_ids) ? 'gray' : 'info'),
                TextColumn::make('offset_hours')
                    ->label('Sati pre')
                    ->placeholder('—'),
                IconColumn::make('active')
                    ->label('Aktivan')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Događaj')
                    ->options(MessageTemplate::EVENTS),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()->label('Izmeni'),
                \Filament\Actions\DeleteAction::make()->label('Obriši')->modalHeading('Brisanje šablona'),
            ])
            ->defaultSort('event');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessageTemplates::route('/'),
            'create' => CreateMessageTemplate::route('/create'),
            'edit' => EditMessageTemplate::route('/{record}/edit'),
        ];
    }
}
