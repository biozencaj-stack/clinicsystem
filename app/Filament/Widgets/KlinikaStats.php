<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Message;
use App\Models\Nalaz;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KlinikaStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return ! auth()->user()?->isDoctor();
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Termini danas', Appointment::whereDate('starts_at', today())
                ->whereNotIn('status', ['otkazan'])
                ->count())
                ->description('zakazani i potvrđeni za danas — otvori kalendar')
                ->icon('heroicon-o-calendar-days')
                ->color('primary')
                ->url(url('/admin/kalendar')),

            Stat::make('Zahtevi na čekanju', Appointment::where('status', 'zahtev')->count())
                ->description('sa sajta i WhatsApp bota — klikni za potvrdu')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->url(url('/admin/appointments?tableFilters[status][value]=zahtev')),

            Stat::make('Pacijenti', Patient::count())
                ->description(Nalaz::whereDate('created_at', '>=', now()->subDays(30))->count() . ' nalaza u poslednjih 30 dana')
                ->icon('heroicon-o-users')
                ->color('success')
                ->url(url('/admin/patients')),

            Stat::make('Poruke (7 dana)', Message::where('created_at', '>=', now()->subDays(7))->count())
                ->description('WhatsApp · Viber · e-mail')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->url(url('/admin/messages')),
        ];
    }
}
