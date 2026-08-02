<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DanasnjiTermini extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Današnji termini')
            ->query(
                Appointment::query()
                    ->with(['patient', 'doctor', 'service'])
                    ->whereDate('starts_at', today())
                    ->when(
                        auth()->user()?->isDoctor(),
                        fn ($q) => $q->where('doctor_id', auth()->user()->doctor_id)
                    )
                    ->orderBy('starts_at')
            )
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Vreme')
                    ->dateTime('H:i'),
                TextColumn::make('patient.full_name')
                    ->label('Pacijent'),
                TextColumn::make('doctor.name')
                    ->label('Doktor'),
                TextColumn::make('service.name')
                    ->label('Usluga'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Appointment::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'zahtev' => 'warning',
                        'zakazan' => 'info',
                        'potvrdjen' => 'success',
                        'otkazan', 'nije_dosao' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading('Danas nema zakazanih termina');
    }
}
