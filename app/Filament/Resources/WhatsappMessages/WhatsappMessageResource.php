<?php

namespace App\Filament\Resources\WhatsappMessages;

use App\Filament\Resources\WhatsappMessages\Pages\CreateWhatsappMessage;
use App\Filament\Resources\WhatsappMessages\Pages\EditWhatsappMessage;
use App\Filament\Resources\WhatsappMessages\Pages\ListWhatsappMessages;
use App\Filament\Resources\WhatsappMessages\Schemas\WhatsappMessageForm;
use App\Filament\Resources\WhatsappMessages\Tables\WhatsappMessagesTable;
use App\Models\WhatsappMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WhatsappMessageResource extends Resource
{
    protected static ?string $model = WhatsappMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Komunikacija';

    protected static ?string $navigationLabel = 'WhatsApp poruke';

    protected static ?string $modelLabel = 'WhatsApp poruka';

    protected static ?string $pluralModelLabel = 'WhatsApp poruke';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return WhatsappMessageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsappMessagesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWhatsappMessages::route('/'),
            'create' => CreateWhatsappMessage::route('/create'),
            'edit' => EditWhatsappMessage::route('/{record}/edit'),
        ];
    }
}
