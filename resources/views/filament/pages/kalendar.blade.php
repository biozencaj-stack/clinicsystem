<x-filament-panels::page>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <x-filament::button color="gray" size="sm" wire:click="previousWeek" icon="heroicon-o-chevron-left">
                Prethodna
            </x-filament::button>
            <x-filament::button color="gray" size="sm" wire:click="currentWeek">
                Ova nedelja
            </x-filament::button>
            <x-filament::button color="gray" size="sm" wire:click="nextWeek" icon="heroicon-o-chevron-right" icon-position="after">
                Sledeća
            </x-filament::button>
            <span class="ml-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                {{ $weekStart->format('d.m.Y.') }} — {{ $weekEnd->format('d.m.Y.') }}
            </span>
        </div>

        <div class="w-56">
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="doctorId">
                    <option value="">Svi doktori</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-7">
        @foreach ($days as $day)
            <div @class([
                'rounded-xl border p-2 min-h-[10rem] bg-white dark:bg-gray-900',
                'border-primary-400 ring-2 ring-primary-200 dark:ring-primary-800' => $day['isToday'],
                'border-gray-200 dark:border-gray-700' => ! $day['isToday'],
            ])>
                <div class="mb-2 text-center">
                    <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ ['Ponedeljak', 'Utorak', 'Sreda', 'Četvrtak', 'Petak', 'Subota', 'Nedelja'][$loop->index] }}
                    </div>
                    <div @class([
                        'text-sm font-semibold',
                        'text-primary-600 dark:text-primary-400' => $day['isToday'],
                    ])>
                        {{ $day['date']->format('d.m.') }}
                    </div>
                </div>

                <div class="space-y-1.5">
                    @forelse ($day['appointments'] as $a)
                        <a href="{{ route('filament.admin.resources.appointments.edit', $a) }}"
                           @class([
                               'block rounded-lg border-l-4 px-2 py-1.5 text-xs bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700',
                               'opacity-50 line-through' => $a->status === 'otkazan',
                           ])
                           style="border-left-color: {{ $a->doctor?->color ?? '#0E6E6B' }}">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $a->starts_at->format('H:i') }} · {{ $a->patient?->full_name }}
                            </div>
                            <div class="text-gray-600 dark:text-gray-300">{{ $a->service?->name }}</div>
                            <div class="text-gray-500 dark:text-gray-400">{{ $a->doctor?->full_name }}</div>
                            <div @class([
                                'mt-0.5 font-medium',
                                'text-amber-600' => $a->status === 'zahtev',
                                'text-blue-600' => $a->status === 'zakazan',
                                'text-green-600' => $a->status === 'potvrdjen',
                                'text-gray-400' => in_array($a->status, ['zavrsen', 'otkazan', 'nije_dosao']),
                            ])>
                                {{ $statusLabels[$a->status] ?? $a->status }}
                            </div>
                        </a>
                    @empty
                        <div class="py-4 text-center text-xs text-gray-400 dark:text-gray-500">—</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
