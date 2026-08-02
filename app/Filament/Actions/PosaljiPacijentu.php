<?php

namespace App\Filament\Actions;

use App\Models\Message;
use App\Models\Nalaz;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class PosaljiPacijentu
{
    /**
     * Akcija koja pacijentu šalje bezbedan link ka dokumentu preko
     * njegovog kanala (WhatsApp → Viber → e-mail).
     */
    public static function make(string $docLabel, Closure $url, string $type = 'dokument'): Action
    {
        return Action::make('posalji')
            ->label('Pošalji pacijentu')
            ->icon('heroicon-o-paper-airplane')
            ->requiresConfirmation()
            ->modalHeading("Slanje pacijentu — {$docLabel}")
            ->modalDescription(function ($record) {
                $route = Message::resolveChannel($record->patient);

                return $route
                    ? 'Pacijent će dobiti bezbedan link za preuzimanje preko kanala '
                        . Message::CHANNELS[$route[0]] . ' (' . $route[1] . '). '
                        . 'Sam dokument se ne šalje kroz poruku — samo zaštićen link.'
                    : 'Pacijent nema saglasnost ni za jedan kanal obaveštavanja — slanje nije moguće. '
                        . 'Saglasnosti se uključuju u kartici pacijenta.';
            })
            ->modalSubmitActionLabel('Pošalji')
            ->action(function ($record) use ($url, $type) {
                $message = Message::sendDocument($record->patient, $record->title, $url($record), $type);

                if (! $message) {
                    Notification::make()
                        ->danger()
                        ->title('Slanje nije moguće')
                        ->body('Pacijent nema saglasnost ni za jedan kanal obaveštavanja.')
                        ->send();

                    return;
                }

                if ($record instanceof Nalaz) {
                    $record->forceFill(['ready_notified_at' => now()])->saveQuietly();
                }

                Notification::make()
                    ->success()
                    ->title('Poslato pacijentu')
                    ->body('Kanal: ' . Message::CHANNELS[$message->channel]
                        . ' (' . $message->destination . '). U demo režimu poruka se samo beleži u sistemu.')
                    ->send();
            });
    }
}
