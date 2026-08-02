<x-filament-panels::page>
    <style>
        .mm-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
        }
        .mm-toolbar-left { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem; }
        .mm-range { font-size: .875rem; font-weight: 600; color: #4A5D62; margin-left: .5rem; }
        .dark .mm-range { color: #97ACA8; }
        .mm-doctor-filter { width: 15rem; }

        .mm-cal-scroll { overflow-x: auto; padding-bottom: .5rem; }
        .mm-cal {
            display: grid;
            grid-template-columns: repeat(7, minmax(160px, 1fr));
            gap: .65rem;
            min-width: 1150px;
        }
        .mm-day {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: .75rem;
            min-height: 13rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .dark .mm-day { background: #111827; border-color: #374151; }
        .mm-day.mm-today { border-color: #0E6E6B; box-shadow: 0 0 0 2px rgba(14, 110, 107, .25); }
        .mm-day.mm-weekend .mm-day-head { background: #F3F4F6; }
        .dark .mm-day.mm-weekend .mm-day-head { background: #1F2937; }

        .mm-day-head {
            text-align: center;
            padding: .5rem .25rem;
            border-bottom: 1px solid #E5E7EB;
            background: #F9FAFB;
        }
        .dark .mm-day-head { border-color: #374151; background: #1F2937; }
        .mm-day-name {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #6B7280;
        }
        .dark .mm-day-name { color: #9CA3AF; }
        .mm-day-date { font-size: .95rem; font-weight: 700; color: #111827; }
        .dark .mm-day-date { color: #F9FAFB; }
        .mm-today .mm-day-name, .mm-today .mm-day-date { color: #0E6E6B; }
        .dark .mm-today .mm-day-name, .dark .mm-today .mm-day-date { color: #53B3AB; }

        .mm-day-body { padding: .5rem; display: flex; flex-direction: column; gap: .4rem; flex: 1; }
        .mm-empty { margin: auto; color: #D1D5DB; font-size: .8rem; }
        .dark .mm-empty { color: #4B5563; }

        .mm-apt {
            display: block;
            border-radius: .5rem;
            border: 1px solid #E5E7EB;
            border-left-width: 4px;
            background: #FAFAFA;
            padding: .45rem .55rem;
            font-size: .72rem;
            line-height: 1.35;
            text-decoration: none;
            transition: background .1s ease, transform .1s ease;
        }
        .mm-apt:hover { background: #F0F6F5; transform: translateY(-1px); }
        .dark .mm-apt { background: #1F2937; border-color: #374151; }
        .dark .mm-apt:hover { background: #253345; }
        .mm-apt.mm-otkazan { opacity: .5; }
        .mm-apt.mm-otkazan .mm-apt-time { text-decoration: line-through; }

        .mm-apt-time { font-weight: 700; color: #111827; font-size: .78rem; }
        .dark .mm-apt-time { color: #F9FAFB; }
        .mm-apt-patient { font-weight: 600; color: #1F2937; }
        .dark .mm-apt-patient { color: #E5E7EB; }
        .mm-apt-service { color: #6B7280; }
        .dark .mm-apt-service { color: #9CA3AF; }
        .mm-apt-doctor { color: #6B7280; font-size: .68rem; }
        .dark .mm-apt-doctor { color: #9CA3AF; }

        .mm-badge {
            display: inline-block;
            margin-top: .3rem;
            padding: .08rem .45rem;
            border-radius: 999px;
            font-size: .62rem;
            font-weight: 600;
        }
        .mm-badge.zahtev { background: #FEF3C7; color: #92400E; }
        .mm-badge.zakazan { background: #DBEAFE; color: #1E40AF; }
        .mm-badge.potvrdjen { background: #D1FAE5; color: #065F46; }
        .mm-badge.zavrsen { background: #F3F4F6; color: #6B7280; }
        .mm-badge.otkazan, .mm-badge.nije_dosao { background: #FEE2E2; color: #991B1B; }
        .dark .mm-badge.zahtev { background: #422F09; color: #FCD34D; }
        .dark .mm-badge.zakazan { background: #172A54; color: #93C5FD; }
        .dark .mm-badge.potvrdjen { background: #093F2E; color: #6EE7B7; }
        .dark .mm-badge.zavrsen { background: #1F2937; color: #9CA3AF; }
        .dark .mm-badge.otkazan, .dark .mm-badge.nije_dosao { background: #450E0E; color: #FCA5A5; }

        .mm-legend { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: .25rem; }
        .mm-legend-item { display: flex; align-items: center; gap: .35rem; font-size: .72rem; color: #6B7280; }
        .dark .mm-legend-item { color: #9CA3AF; }
        .mm-legend-dot { width: .65rem; height: .65rem; border-radius: 999px; display: inline-block; }
    </style>

    <div class="mm-toolbar">
        <div class="mm-toolbar-left">
            <x-filament::button color="gray" size="sm" wire:click="previousWeek" icon="heroicon-o-chevron-left">
                Prethodna
            </x-filament::button>
            <x-filament::button color="gray" size="sm" wire:click="currentWeek">
                Ova nedelja
            </x-filament::button>
            <x-filament::button color="gray" size="sm" wire:click="nextWeek" icon="heroicon-o-chevron-right" icon-position="after">
                Sledeća
            </x-filament::button>
            <span class="mm-range">{{ $weekStart->format('d.m.Y.') }} — {{ $weekEnd->format('d.m.Y.') }}</span>
        </div>

        <div class="mm-doctor-filter">
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

    <div class="mm-cal-scroll">
        <div class="mm-cal">
            @foreach ($days as $day)
                <div @class([
                    'mm-day',
                    'mm-today' => $day['isToday'],
                    'mm-weekend' => $loop->index >= 5,
                ])>
                    <div class="mm-day-head">
                        <div class="mm-day-name">
                            {{ ['Ponedeljak', 'Utorak', 'Sreda', 'Četvrtak', 'Petak', 'Subota', 'Nedelja'][$loop->index] }}
                        </div>
                        <div class="mm-day-date">{{ $day['date']->format('d.m.') }}</div>
                    </div>

                    <div class="mm-day-body">
                        @forelse ($day['appointments'] as $a)
                            <a href="{{ route('filament.admin.resources.appointments.edit', $a) }}"
                               @class(['mm-apt', 'mm-otkazan' => $a->status === 'otkazan'])
                               style="border-left-color: {{ $a->doctor?->color ?? '#0E6E6B' }}">
                                <span class="mm-apt-time">{{ $a->starts_at->format('H:i') }}</span>
                                <span class="mm-apt-patient">· {{ $a->patient?->full_name }}</span>
                                <div class="mm-apt-service">{{ $a->service?->name }}</div>
                                <div class="mm-apt-doctor">{{ $a->doctor?->full_name }}</div>
                                <span class="mm-badge {{ $a->status }}">{{ $statusLabels[$a->status] ?? $a->status }}</span>
                            </a>
                        @empty
                            <div class="mm-empty">Nema termina</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mm-legend">
        @foreach ($doctors as $doctor)
            <span class="mm-legend-item">
                <span class="mm-legend-dot" style="background: {{ $doctor->color }}"></span>
                {{ $doctor->full_name }}
            </span>
        @endforeach
    </div>
</x-filament-panels::page>
