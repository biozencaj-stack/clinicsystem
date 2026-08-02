<x-filament-panels::page>
    <style>
        .gc-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
        }
        .gc-toolbar-left { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem; }
        .gc-range { font-size: 1rem; font-weight: 600; color: #1F2937; margin-left: .5rem; }
        .dark .gc-range { color: #F3F4F6; }
        .gc-doctor-filter { width: 15rem; }

        .gc-scroll { overflow-x: auto; }
        .gc-wrap {
            min-width: 1180px;
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: .75rem;
            overflow: hidden;
        }
        .dark .gc-wrap { background: #111827; border-color: #374151; }

        .gc-grid { display: grid; grid-template-columns: 4rem repeat(7, 1fr); }

        /* Zaglavlje dana — Google stil: dan malim slovima, datum u krugu */
        .gc-head { border-bottom: 1px solid #E5E7EB; }
        .dark .gc-head { border-color: #374151; }
        .gc-head-cell {
            text-align: center;
            padding: .6rem .25rem .5rem;
            border-left: 1px solid #F3F4F6;
        }
        .dark .gc-head-cell { border-color: #1F2937; }
        .gc-head-day {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #6B7280;
            margin-bottom: .2rem;
        }
        .dark .gc-head-day { color: #9CA3AF; }
        .gc-head-date {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.3rem;
            height: 2.3rem;
            border-radius: 999px;
            font-size: 1.15rem;
            font-weight: 500;
            color: #1F2937;
        }
        .dark .gc-head-date { color: #F3F4F6; }
        .gc-today .gc-head-day { color: #0E6E6B; font-weight: 700; }
        .dark .gc-today .gc-head-day { color: #53B3AB; }
        .gc-today .gc-head-date { background: #0E6E6B; color: #fff; }
        .dark .gc-today .gc-head-date { background: #53B3AB; color: #062A28; }

        /* Vremenska osa */
        .gc-gutter { position: relative; }
        .gc-hour-label {
            position: absolute;
            right: .5rem;
            transform: translateY(-50%);
            font-size: .65rem;
            color: #9CA3AF;
            background: inherit;
        }
        .dark .gc-hour-label { color: #6B7280; }

        /* Kolone dana */
        .gc-col {
            position: relative;
            border-left: 1px solid #F3F4F6;
            background-image: repeating-linear-gradient(
                to bottom,
                #F3F4F6 0, #F3F4F6 1px,
                transparent 1px, transparent 60px
            );
        }
        .dark .gc-col {
            border-color: #1F2937;
            background-image: repeating-linear-gradient(
                to bottom,
                #1F2937 0, #1F2937 1px,
                transparent 1px, transparent 60px
            );
        }
        .gc-col.gc-today-col { background-color: rgba(14, 110, 107, .04); }
        .dark .gc-col.gc-today-col { background-color: rgba(83, 179, 171, .06); }

        /* Blok termina */
        .gc-event {
            position: absolute;
            border-radius: .4rem;
            padding: .28rem .45rem;
            font-size: .68rem;
            line-height: 1.3;
            color: #fff;
            overflow: hidden;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .18);
            border-left: 3px solid rgba(255, 255, 255, .55);
            transition: filter .1s ease, box-shadow .1s ease;
        }
        .gc-event:hover { filter: brightness(1.12); box-shadow: 0 2px 6px rgba(0, 0, 0, .3); z-index: 30 !important; }
        .gc-event-time { font-weight: 700; font-size: .66rem; opacity: .95; }
        .gc-event-title { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .gc-event-sub { opacity: .85; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: .63rem; }

        .gc-event.gc-zavrsen { opacity: .55; }
        .gc-event.gc-otkazan { opacity: .45; }
        .gc-event.gc-otkazan .gc-event-title { text-decoration: line-through; }
        .gc-event.gc-zahtev {
            background-image: repeating-linear-gradient(
                45deg,
                rgba(255, 255, 255, .18) 0, rgba(255, 255, 255, .18) 6px,
                transparent 6px, transparent 12px
            );
        }
        .gc-event.gc-nije_dosao { opacity: .45; }

        /* Linija trenutnog vremena */
        .gc-now {
            position: absolute;
            left: 0;
            right: 0;
            height: 2px;
            background: #EA4335;
            z-index: 25;
        }
        .gc-now::before {
            content: '';
            position: absolute;
            left: -5px;
            top: -4px;
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #EA4335;
        }

        .gc-legend { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: .5rem; }
        .gc-legend-item { display: flex; align-items: center; gap: .35rem; font-size: .72rem; color: #6B7280; }
        .dark .gc-legend-item { color: #9CA3AF; }
        .gc-legend-dot { width: .65rem; height: .65rem; border-radius: 999px; display: inline-block; }
        .gc-legend-note { font-size: .72rem; color: #9CA3AF; margin-left: auto; }
    </style>

    <div class="gc-toolbar">
        <div class="gc-toolbar-left">
            <x-filament::icon-button color="gray" wire:click="previousWeek" icon="heroicon-o-chevron-left" label="Prethodna nedelja" />
            <x-filament::button color="gray" size="sm" wire:click="currentWeek">
                Danas
            </x-filament::button>
            <x-filament::icon-button color="gray" wire:click="nextWeek" icon="heroicon-o-chevron-right" label="Sledeća nedelja" />
            <span class="gc-range">
                {{ $weekStart->format('d.') }} {{ $weekStart->format('m.') === $weekEnd->format('m.') ? '' : $weekStart->format('m.') }}
                — {{ $weekEnd->format('d.m.Y.') }}
            </span>
        </div>

        <div class="gc-doctor-filter">
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

    <div class="gc-scroll">
        <div class="gc-wrap">
            {{-- Zaglavlje: dani --}}
            <div class="gc-grid gc-head">
                <div></div>
                @foreach ($days as $day)
                    <div @class(['gc-head-cell', 'gc-today' => $day['isToday']])>
                        <div class="gc-head-day">
                            {{ ['Pon', 'Uto', 'Sre', 'Čet', 'Pet', 'Sub', 'Ned'][$loop->index] }}
                        </div>
                        <span class="gc-head-date">{{ $day['date']->format('j') }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Mreža: sati × dani --}}
            <div class="gc-grid">
                <div class="gc-gutter" style="height: {{ $gridHeight }}px">
                    @foreach ($hours as $h)
                        @if (! $loop->first)
                            <span class="gc-hour-label" style="top: {{ ($h - $hours[0]) * 60 }}px">
                                {{ str_pad((string) $h, 2, '0', STR_PAD_LEFT) }}:00
                            </span>
                        @endif
                    @endforeach
                </div>

                @foreach ($days as $day)
                    <div @class(['gc-col', 'gc-today-col' => $day['isToday']]) style="height: {{ $gridHeight }}px">
                        @foreach ($day['events'] as $e)
                            @php
                                $a = $e['a'];
                                $widthPct = 100 / $e['laneCount'];
                                $leftPct = $e['lane'] * $widthPct;
                            @endphp
                            <a href="{{ route('filament.admin.resources.appointments.edit', $a) }}"
                               @class(['gc-event', 'gc-' . $a->status])
                               style="top: {{ $e['top'] }}px;
                                      height: {{ $e['height'] }}px;
                                      left: calc({{ $leftPct }}% + 2px);
                                      width: calc({{ $widthPct }}% - 5px);
                                      background-color: {{ $a->doctor?->color ?? '#0E6E6B' }};
                                      z-index: {{ 10 + $e['lane'] }};"
                               title="{{ $a->starts_at->format('H:i') }}–{{ $a->ends_at?->format('H:i') }} · {{ $a->patient?->full_name }} · {{ $a->service?->name }} · {{ $a->doctor?->full_name }} · {{ $statusLabels[$a->status] ?? $a->status }}">
                                <span class="gc-event-time">{{ $a->starts_at->format('H:i') }}</span>
                                <div class="gc-event-title">{{ $a->patient?->full_name }}</div>
                                <div class="gc-event-sub">{{ $a->service?->name }}</div>
                                @if ($e['height'] >= 55)
                                    <div class="gc-event-sub">{{ $a->doctor?->full_name }}</div>
                                @endif
                            </a>
                        @endforeach

                        @if ($day['isToday'] && $nowOffset !== null)
                            <div class="gc-now" style="top: {{ $nowOffset }}px"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="gc-legend">
        @foreach ($doctors as $doctor)
            <span class="gc-legend-item">
                <span class="gc-legend-dot" style="background: {{ $doctor->color }}"></span>
                {{ $doctor->full_name }}
            </span>
        @endforeach
        <span class="gc-legend-note">Išrafiran blok = zahtev koji čeka potvrdu · bledi blokovi = završeni ili otkazani</span>
    </div>
</x-filament-panels::page>
