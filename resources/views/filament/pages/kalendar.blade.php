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
        .gc-toolbar-right { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem; }
        .gc-range { font-size: 1rem; font-weight: 600; color: #1F2937; margin-left: .5rem; }
        .dark .gc-range { color: #F3F4F6; }
        .gc-doctor-filter { width: 13rem; }

        /* Prekidač prikaza */
        .gc-modes {
            display: inline-flex;
            border: 1px solid #D1D5DB;
            border-radius: .55rem;
            overflow: hidden;
            background: #fff;
        }
        .dark .gc-modes { border-color: #4B5563; background: #1F2937; }
        .gc-mode-btn {
            padding: .38rem .85rem;
            font-size: .8rem;
            font-weight: 500;
            color: #4B5563;
            background: transparent;
            border: none;
            border-left: 1px solid #E5E7EB;
            cursor: pointer;
        }
        .gc-mode-btn:first-child { border-left: none; }
        .dark .gc-mode-btn { color: #9CA3AF; border-color: #374151; }
        .gc-mode-btn:hover { background: #F3F4F6; }
        .dark .gc-mode-btn:hover { background: #253345; }
        .gc-mode-btn.active { background: #0E6E6B; color: #fff; }
        .dark .gc-mode-btn.active { background: #53B3AB; color: #062A28; }

        .gc-scroll { overflow-x: auto; }
        .gc-wrap {
            min-width: 1180px;
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: .75rem;
            overflow: hidden;
        }
        .dark .gc-wrap { background: #111827; border-color: #374151; }
        .gc-wrap.gc-narrow { min-width: 720px; }

        .gc-grid { display: grid; grid-template-columns: 4rem repeat(var(--gc-cols, 7), 1fr); }

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

        .gc-doc-head { display: flex; align-items: center; justify-content: center; gap: .4rem; padding: .7rem .25rem; }
        .gc-doc-dot { width: .7rem; height: .7rem; border-radius: 999px; flex: none; }
        .gc-doc-name { font-size: .78rem; font-weight: 600; color: #1F2937; }
        .dark .gc-doc-name { color: #F3F4F6; }
        .gc-doc-spec { font-size: .65rem; color: #6B7280; }
        .dark .gc-doc-spec { color: #9CA3AF; }

        .gc-gutter { position: relative; }
        .gc-hour-label {
            position: absolute;
            right: .5rem;
            transform: translateY(-50%);
            font-size: .65rem;
            color: #9CA3AF;
        }
        .dark .gc-hour-label { color: #6B7280; }

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
        .gc-event.gc-nije_dosao { opacity: .45; }
        .gc-event.gc-zahtev {
            background-image: repeating-linear-gradient(
                45deg,
                rgba(255, 255, 255, .18) 0, rgba(255, 255, 255, .18) 6px,
                transparent 6px, transparent 12px
            );
        }

        .gc-now { position: absolute; left: 0; right: 0; height: 2px; background: #EA4335; z-index: 25; }
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

        /* Mesec */
        .gc-month { display: grid; grid-template-columns: repeat(7, 1fr); }
        .gc-month-dayname {
            text-align: center;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #6B7280;
            padding: .5rem 0;
            border-bottom: 1px solid #E5E7EB;
        }
        .dark .gc-month-dayname { color: #9CA3AF; border-color: #374151; }
        .gc-month-cell {
            min-height: 6.5rem;
            border-left: 1px solid #F3F4F6;
            border-top: 1px solid #F3F4F6;
            padding: .3rem;
        }
        .dark .gc-month-cell { border-color: #1F2937; }
        .gc-month-cell.gc-out { background: #FAFAFA; }
        .dark .gc-month-cell.gc-out { background: #0D141F; }
        .gc-month-date {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: .2rem;
        }
        .dark .gc-month-date { color: #F3F4F6; }
        .gc-out .gc-month-date { color: #9CA3AF; font-weight: 400; }
        .gc-month-cell.gc-today-cell .gc-month-date { background: #0E6E6B; color: #fff; }
        .dark .gc-month-cell.gc-today-cell .gc-month-date { background: #53B3AB; color: #062A28; }
        .gc-chip {
            display: block;
            border-radius: .3rem;
            padding: .12rem .35rem;
            margin-bottom: .18rem;
            font-size: .63rem;
            font-weight: 500;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-decoration: none;
        }
        .gc-chip:hover { filter: brightness(1.12); }
        .gc-chip.gc-otkazan { opacity: .45; text-decoration: line-through; }
        .gc-chip.gc-zavrsen { opacity: .55; }
        .gc-more { font-size: .63rem; color: #6B7280; padding-left: .35rem; }
        .dark .gc-more { color: #9CA3AF; }

        /* Lista */
        .gc-list { display: flex; flex-direction: column; }
        .gc-list-day {
            display: flex;
            gap: 1rem;
            padding: .8rem 1rem;
            border-top: 1px solid #F3F4F6;
        }
        .dark .gc-list-day { border-color: #1F2937; }
        .gc-list-day:first-child { border-top: none; }
        .gc-list-datebox { width: 6.5rem; flex: none; text-align: center; }
        .gc-list-datenum {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 999px;
            font-size: 1.2rem;
            font-weight: 600;
            color: #1F2937;
        }
        .dark .gc-list-datenum { color: #F3F4F6; }
        .gc-list-day.gc-today-row .gc-list-datenum { background: #0E6E6B; color: #fff; }
        .dark .gc-list-day.gc-today-row .gc-list-datenum { background: #53B3AB; color: #062A28; }
        .gc-list-dayname { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; color: #6B7280; margin-top: .15rem; }
        .dark .gc-list-dayname { color: #9CA3AF; }
        .gc-list-items { flex: 1; display: flex; flex-direction: column; gap: .35rem; }
        .gc-list-item {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .45rem .7rem;
            border-radius: .5rem;
            background: #F9FAFB;
            text-decoration: none;
            font-size: .8rem;
            color: #1F2937;
        }
        .gc-list-item:hover { background: #F0F6F5; }
        .dark .gc-list-item { background: #1F2937; color: #E5E7EB; }
        .dark .gc-list-item:hover { background: #253345; }
        .gc-list-dot { width: .7rem; height: .7rem; border-radius: 999px; flex: none; }
        .gc-list-time { font-weight: 700; font-variant-numeric: tabular-nums; width: 6.2rem; flex: none; }
        .gc-list-patient { font-weight: 600; width: 13rem; flex: none; }
        .gc-list-service { color: #6B7280; flex: 1; }
        .dark .gc-list-service { color: #9CA3AF; }
        .gc-list-doctor { color: #6B7280; width: 14rem; flex: none; font-size: .74rem; }
        .dark .gc-list-doctor { color: #9CA3AF; }
        .gc-list-status { font-size: .66rem; font-weight: 600; padding: .1rem .5rem; border-radius: 999px; flex: none; }
        .st-zahtev { background: #FEF3C7; color: #92400E; }
        .st-zakazan { background: #DBEAFE; color: #1E40AF; }
        .st-potvrdjen { background: #D1FAE5; color: #065F46; }
        .st-zavrsen { background: #F3F4F6; color: #6B7280; }
        .st-otkazan, .st-nije_dosao { background: #FEE2E2; color: #991B1B; }

        /* Mini kalendar (popover za izbor datuma) */
        [x-cloak] { display: none !important; }
        .gc-picker-wrap { position: relative; display: inline-block; }
        .gc-range-btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: .25rem .5rem;
            border-radius: .5rem;
        }
        .gc-range-btn:hover { background: #F3F4F6; }
        .dark .gc-range-btn:hover { background: #1F2937; }
        .gc-range-caret { width: .85rem; height: .85rem; color: #9CA3AF; }
        .gc-picker {
            position: absolute;
            top: calc(100% + .4rem);
            left: 0;
            z-index: 50;
            width: 17rem;
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: .75rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .12);
            padding: .75rem;
        }
        .dark .gc-picker { background: #1F2937; border-color: #374151; box-shadow: 0 10px 25px rgba(0, 0, 0, .45); }
        .gc-picker-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .5rem; }
        .gc-picker-title { font-size: .85rem; font-weight: 600; color: #1F2937; text-transform: capitalize; }
        .dark .gc-picker-title { color: #F3F4F6; }
        .gc-picker-nav {
            border: none;
            background: transparent;
            cursor: pointer;
            padding: .3rem .5rem;
            border-radius: .4rem;
            color: #6B7280;
            font-size: .85rem;
            line-height: 1;
        }
        .gc-picker-nav:hover { background: #F3F4F6; color: #111827; }
        .dark .gc-picker-nav:hover { background: #374151; color: #F9FAFB; }
        .gc-picker-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: .1rem; }
        .gc-picker-dayname {
            text-align: center;
            font-size: .6rem;
            text-transform: uppercase;
            color: #9CA3AF;
            padding: .2rem 0;
        }
        .gc-picker-day {
            border: none;
            background: transparent;
            cursor: pointer;
            width: 2.1rem;
            height: 2.1rem;
            border-radius: 999px;
            font-size: .75rem;
            color: #1F2937;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dark .gc-picker-day { color: #E5E7EB; }
        .gc-picker-day:hover { background: #F3F4F6; }
        .dark .gc-picker-day:hover { background: #374151; }
        .gc-picker-day.out { color: #C4CBD1; }
        .dark .gc-picker-day.out { color: #4B5563; }
        .gc-picker-day.today { box-shadow: inset 0 0 0 1.5px #0E6E6B; font-weight: 700; }
        .dark .gc-picker-day.today { box-shadow: inset 0 0 0 1.5px #53B3AB; }
        .gc-picker-day.selected { background: #0E6E6B; color: #fff; font-weight: 700; }
        .dark .gc-picker-day.selected { background: #53B3AB; color: #062A28; }
        .gc-picker-foot { display: flex; justify-content: flex-end; margin-top: .5rem; }
        .gc-picker-today-btn {
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: .75rem;
            font-weight: 600;
            color: #0E6E6B;
            padding: .3rem .6rem;
            border-radius: .4rem;
        }
        .gc-picker-today-btn:hover { background: #E3F0EE; }
        .dark .gc-picker-today-btn { color: #53B3AB; }
        .dark .gc-picker-today-btn:hover { background: #253345; }

        .gc-legend { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: .5rem; }
        .gc-legend-item { display: flex; align-items: center; gap: .35rem; font-size: .72rem; color: #6B7280; }
        .dark .gc-legend-item { color: #9CA3AF; }
        .gc-legend-dot { width: .65rem; height: .65rem; border-radius: 999px; display: inline-block; }
        .gc-legend-note { font-size: .72rem; color: #9CA3AF; margin-left: auto; }
        .gc-empty-note { padding: 2.5rem; text-align: center; color: #9CA3AF; font-size: .85rem; }
    </style>

    <div class="gc-toolbar">
        <div class="gc-toolbar-left">
            <x-filament::icon-button color="gray" wire:click="previous" icon="heroicon-o-chevron-left" label="Prethodni period" />
            <x-filament::button color="gray" size="sm" wire:click="today">
                Danas
            </x-filament::button>
            <x-filament::icon-button color="gray" wire:click="next" icon="heroicon-o-chevron-right" label="Sledeći period" />

            <div class="gc-picker-wrap" x-data="{ pickerOpen: false }">
                <button type="button" class="gc-range-btn" @click="pickerOpen = ! pickerOpen">
                    <span class="gc-range" style="margin-left: 0">{{ $rangeLabel }}</span>
                    <svg class="gc-range-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div class="gc-picker" x-show="pickerOpen" x-cloak @click.outside="pickerOpen = false">
                    <div class="gc-picker-head">
                        <button type="button" class="gc-picker-nav" wire:click="pickerPrev">‹</button>
                        <span class="gc-picker-title">{{ $picker['label'] }}</span>
                        <button type="button" class="gc-picker-nav" wire:click="pickerNext">›</button>
                    </div>

                    <div class="gc-picker-grid">
                        @foreach (['P', 'U', 'S', 'Č', 'P', 'S', 'N'] as $dn)
                            <span class="gc-picker-dayname">{{ $dn }}</span>
                        @endforeach

                        @foreach ($picker['weeks'] as $week)
                            @foreach ($week as $cell)
                                <button type="button"
                                        @class([
                                            'gc-picker-day',
                                            'out' => ! $cell['inMonth'],
                                            'today' => $cell['isToday'],
                                            'selected' => $cell['isAnchor'],
                                        ])
                                        wire:click="goTo('{{ $cell['date'] }}')"
                                        @click="pickerOpen = false">
                                    {{ $cell['day'] }}
                                </button>
                            @endforeach
                        @endforeach
                    </div>

                    <div class="gc-picker-foot">
                        <button type="button" class="gc-picker-today-btn" wire:click="today" @click="pickerOpen = false">
                            Danas
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="gc-toolbar-right">
            <div class="gc-modes">
                @foreach ($modes as $key => $label)
                    <button type="button"
                            class="gc-mode-btn {{ $mode === $key ? 'active' : '' }}"
                            wire:click="setMode('{{ $key }}')">
                        {{ $label }}
                    </button>
                @endforeach
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
    </div>

    {{-- ======================= NEDELJA ======================= --}}
    @if ($mode === 'nedelja')
        <div class="gc-scroll">
            <div class="gc-wrap" style="--gc-cols: 7">
                <div class="gc-grid gc-head">
                    <div></div>
                    @foreach ($days as $day)
                        <div @class(['gc-head-cell', 'gc-today' => $day['isToday']])>
                            <div class="gc-head-day">{{ ['Pon', 'Uto', 'Sre', 'Čet', 'Pet', 'Sub', 'Ned'][$loop->index] }}</div>
                            <span class="gc-head-date">{{ $day['date']->format('j') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="gc-grid">
                    <div class="gc-gutter" style="height: {{ $gridHeight }}px">
                        @foreach ($hours as $h)
                            @unless ($loop->first)
                                <span class="gc-hour-label" style="top: {{ ($h - $hours[0]) * 60 }}px">{{ sprintf('%02d:00', $h) }}</span>
                            @endunless
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
                                   style="top: {{ $e['top'] }}px; height: {{ $e['height'] }}px; left: calc({{ $leftPct }}% + 2px); width: calc({{ $widthPct }}% - 5px); background-color: {{ $a->doctor?->color ?? '#0E6E6B' }}; z-index: {{ 10 + $e['lane'] }};"
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
    @endif

    {{-- ======================= DAN (kolona po doktoru) ======================= --}}
    @if ($mode === 'dan')
        <div class="gc-scroll">
            <div class="gc-wrap {{ $columns->count() <= 4 ? 'gc-narrow' : '' }}" style="--gc-cols: {{ max($columns->count(), 1) }}">
                <div class="gc-grid gc-head">
                    <div></div>
                    @forelse ($columns as $col)
                        <div class="gc-head-cell">
                            <div class="gc-doc-head">
                                <span class="gc-doc-dot" style="background: {{ $col['doctor']->color }}"></span>
                                <span>
                                    <div class="gc-doc-name">{{ $col['doctor']->full_name }}</div>
                                    <div class="gc-doc-spec">{{ $col['doctor']->specialty }}</div>
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="gc-empty-note">Nema aktivnih doktora.</div>
                    @endforelse
                </div>

                <div class="gc-grid">
                    <div class="gc-gutter" style="height: {{ $gridHeight }}px">
                        @foreach ($hours as $h)
                            @unless ($loop->first)
                                <span class="gc-hour-label" style="top: {{ ($h - $hours[0]) * 60 }}px">{{ sprintf('%02d:00', $h) }}</span>
                            @endunless
                        @endforeach
                    </div>

                    @foreach ($columns as $col)
                        <div @class(['gc-col', 'gc-today-col' => $isToday]) style="height: {{ $gridHeight }}px">
                            @foreach ($col['events'] as $e)
                                @php
                                    $a = $e['a'];
                                    $widthPct = 100 / $e['laneCount'];
                                    $leftPct = $e['lane'] * $widthPct;
                                @endphp
                                <a href="{{ route('filament.admin.resources.appointments.edit', $a) }}"
                                   @class(['gc-event', 'gc-' . $a->status])
                                   style="top: {{ $e['top'] }}px; height: {{ $e['height'] }}px; left: calc({{ $leftPct }}% + 2px); width: calc({{ $widthPct }}% - 5px); background-color: {{ $col['doctor']->color }}; z-index: {{ 10 + $e['lane'] }};"
                                   title="{{ $a->starts_at->format('H:i') }}–{{ $a->ends_at?->format('H:i') }} · {{ $a->patient?->full_name }} · {{ $a->service?->name }} · {{ $statusLabels[$a->status] ?? $a->status }}">
                                    <span class="gc-event-time">{{ $a->starts_at->format('H:i') }}–{{ $a->ends_at?->format('H:i') }}</span>
                                    <div class="gc-event-title">{{ $a->patient?->full_name }}</div>
                                    <div class="gc-event-sub">{{ $a->service?->name }}</div>
                                </a>
                            @endforeach

                            @if ($isToday && $nowOffset !== null)
                                <div class="gc-now" style="top: {{ $nowOffset }}px"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ======================= MESEC ======================= --}}
    @if ($mode === 'mesec')
        <div class="gc-scroll">
            <div class="gc-wrap gc-narrow">
                <div class="gc-month">
                    @foreach (['Pon', 'Uto', 'Sre', 'Čet', 'Pet', 'Sub', 'Ned'] as $dn)
                        <div class="gc-month-dayname">{{ $dn }}</div>
                    @endforeach

                    @foreach ($weeks as $week)
                        @foreach ($week as $cell)
                            <div @class([
                                'gc-month-cell',
                                'gc-out' => ! $cell['inMonth'],
                                'gc-today-cell' => $cell['isToday'],
                            ])>
                                <span class="gc-month-date">{{ $cell['date']->format('j') }}</span>
                                @foreach ($cell['appointments']->take(3) as $a)
                                    <a href="{{ route('filament.admin.resources.appointments.edit', $a) }}"
                                       @class(['gc-chip', 'gc-' . $a->status])
                                       style="background: {{ $a->doctor?->color ?? '#0E6E6B' }}"
                                       title="{{ $a->starts_at->format('H:i') }} · {{ $a->patient?->full_name }} · {{ $a->service?->name }} · {{ $a->doctor?->full_name }}">
                                        {{ $a->starts_at->format('H:i') }} {{ $a->patient?->full_name }}
                                    </a>
                                @endforeach
                                @if ($cell['appointments']->count() > 3)
                                    <span class="gc-more">+ još {{ $cell['appointments']->count() - 3 }}</span>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ======================= LISTA ======================= --}}
    @if ($mode === 'lista')
        <div class="gc-wrap gc-narrow" style="min-width: 0">
            <div class="gc-list">
                @forelse ($groups as $group)
                    <div @class(['gc-list-day', 'gc-today-row' => $group['isToday']])>
                        <div class="gc-list-datebox">
                            <span class="gc-list-datenum">{{ $group['date']->format('j') }}</span>
                            <div class="gc-list-dayname">{{ $group['dayName'] }}</div>
                        </div>
                        <div class="gc-list-items">
                            @foreach ($group['appointments'] as $a)
                                <a href="{{ route('filament.admin.resources.appointments.edit', $a) }}" class="gc-list-item">
                                    <span class="gc-list-dot" style="background: {{ $a->doctor?->color ?? '#0E6E6B' }}"></span>
                                    <span class="gc-list-time">{{ $a->starts_at->format('H:i') }}–{{ $a->ends_at?->format('H:i') }}</span>
                                    <span class="gc-list-patient">{{ $a->patient?->full_name }}</span>
                                    <span class="gc-list-service">{{ $a->service?->name }}</span>
                                    <span class="gc-list-doctor">{{ $a->doctor?->full_name }}</span>
                                    <span class="gc-list-status st-{{ $a->status }}">{{ $statusLabels[$a->status] ?? $a->status }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="gc-empty-note">Nema termina u izabranoj nedelji.</div>
                @endforelse
            </div>
        </div>
    @endif

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
