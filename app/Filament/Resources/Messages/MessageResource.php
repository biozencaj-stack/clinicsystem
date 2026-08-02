<?php

namespace App\Filament\Resources\Messages;

use App\Filament\Resources\Messages\Pages\EditMessage;
use App\Filament\Resources\Messages\Pages\ListMessages;
use App\Filament\Resources\Messages\Schemas\MessageForm;
use App\Filament\Resources\Messages\Tables\MessagesTable;
use App\Models\Message;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Komunikacija';

    protected static ?string $navigationLabel = 'Poruke';

    protected static ?string $modelLabel = 'poruka';

    protected static ?string $pluralModelLabel = 'Poruke';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return ! auth()->user()?->isDoctor();
    }

    public static function form(Schema $schema): Schema
    {
        return MessageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessagesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessages::route('/'),
            'edit' => EditMessage::route('/{record}/edit'),
        ];
    }
}
