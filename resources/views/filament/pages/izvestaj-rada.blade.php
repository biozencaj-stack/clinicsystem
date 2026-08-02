<x-filament-panels::page>
    <style>
        .ir-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; }
        .ir-toolbar-left { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem; }
        .ir-range-label { font-size: .95rem; font-weight: 600; color: #1F2937; }
        .dark .ir-range-label { color: #F3F4F6; }
        .ir-date-input {
            border: 1px solid #D1D5DB; border-radius: .55rem; padding: .4rem .6rem;
            font-size: .82rem; background: #fff; color: #1F2937;
        }
        .dark .ir-date-input { background: #1F2937; border-color: #4B5563; color: #E5E7EB; }
        .ir-sep { color: #9CA3AF; }

        .ir-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr)); gap: .75rem; }
        .ir-sum-card { background: #fff; border: 1px solid #E5E7EB; border-radius: .75rem; padding: .9rem 1.1rem; }
        .dark .ir-sum-card { background: #111827; border-color: #374151; }
        .ir-sum-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .07em; color: #6B7280; }
        .dark .ir-sum-label { color: #9CA3AF; }
        .ir-sum-value { font-size: 1.5rem; font-weight: 700; color: #111827; font-variant-numeric: tabular-nums; }
        .dark .ir-sum-value { color: #F9FAFB; }
        .ir-sum-value.green { color: #0E6E6B; }
        .dark .ir-sum-value.green { color: #53B3AB; }

        .ir-doc { background: #fff; border: 1px solid #E5E7EB; border-radius: .75rem; overflow: hidden; }
        .dark .ir-doc { background: #111827; border-color: #374151; }
        .ir-doc-head {
            display: flex; flex-wrap: wrap; align-items: center; gap: .6rem;
            padding: .8rem 1rem; border-bottom: 1px solid #E5E7EB; background: #F9FAFB;
        }
        .dark .ir-doc-head { background: #1F2937; border-color: #374151; }
        .ir-doc-dot { width: .8rem; height: .8rem; border-radius: 999px; flex: none; }
        .ir-doc-name { font-weight: 700; color: #111827; }
        .dark .ir-doc-name { color: #F9FAFB; }
        .ir-doc-spec { font-size: .75rem; color: #6B7280; }
        .dark .ir-doc-spec { color: #9CA3AF; }
        .ir-doc-stats { margin-left: auto; display: flex; gap: 1rem; font-size: .78rem; color: #4B5563; font-variant-numeric: tabular-nums; }
        .dark .ir-doc-stats { color: #9CA3AF; }
        .ir-doc-stats b { color: #0E6E6B; }
        .dark .ir-doc-stats b { color: #53B3AB; }

        .ir-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
        .ir-table th {
            text-align: left; padding: .45rem 1rem; font-size: .68rem; text-transform: uppercase;
            letter-spacing: .06em; color: #6B7280; border-bottom: 1px solid #F3F4F6;
        }
        .dark .ir-table th { color: #9CA3AF; border-color: #1F2937; }
        .ir-table td { padding: .5rem 1rem; border-bottom: 1px solid #F3F4F6; color: #1F2937; }
        .dark .ir-table td { border-color: #1F2937; color: #E5E7EB; }
        .ir-table tr:last-child td { border-bottom: none; }
        .ir-num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .ir-bar { display: inline-block; height: .55rem; border-radius: 999px; background: #0E6E6B; vertical-align: middle; margin-right: .5rem; opacity: .75; }
        .dark .ir-bar { background: #53B3AB; }
        .ir-empty { padding: 1rem; font-size: .82rem; color: #9CA3AF; }
        .ir-total-row td { font-weight: 700; background: #F0F6F5; }
        .dark .ir-total-row td { background: #16302E; }
    </style>

    <div class="ir-toolbar">
        <div class="ir-toolbar-left">
            <input type="date" class="ir-date-input" wire:model.live="dateFrom">
            <span class="ir-sep">—</span>
            <input type="date" class="ir-date-input" wire:model.live="dateTo">
            <x-filament::button color="gray" size="sm" wire:click="thisMonth">Ovaj mesec</x-filament::button>
            <x-filament::button color="gray" size="sm" wire:click="lastMonth">Prošli mesec</x-filament::button>
            <x-filament::button color="gray" size="sm" wire:click="last30">Poslednjih 30 dana</x-filament::button>
        </div>

        <x-filament::button tag="a" href="{{ $printUrl }}" target="_blank" icon="heroicon-o-printer" color="gray">
            Štampaj izveštaj (PDF)
        </x-filament::button>
    </div>

    <div class="ir-range-label">Period: {{ $rangeLabel }}</div>

    <div class="ir-summary">
        <div class="ir-sum-card">
            <div class="ir-sum-label">Završenih pregleda</div>
            <div class="ir-sum-value green">{{ $total['zavrseno'] }}</div>
        </div>
        <div class="ir-sum-card">
            <div class="ir-sum-label">Predstojećih u periodu</div>
            <div class="ir-sum-value">{{ $total['predstojeci'] }}</div>
        </div>
        <div class="ir-sum-card">
            <div class="ir-sum-label">Nedolasci</div>
            <div class="ir-sum-value">{{ $total['nije_dosao'] }}</div>
        </div>
        <div class="ir-sum-card">
            <div class="ir-sum-label">Otkazano / odbijeno</div>
            <div class="ir-sum-value">{{ $total['otkazano'] }}</div>
        </div>
    </div>

    @php
        $maxCount = collect($doctors)->flatMap(fn ($r) => collect($r['services'])->pluck('count'))->max() ?: 1;
    @endphp

    @foreach ($doctors as $row)
        <div class="ir-doc">
            <div class="ir-doc-head">
                <span class="ir-doc-dot" style="background: {{ $row['doctor']->color }}"></span>
                <span>
                    <div class="ir-doc-name">{{ $row['doctor']->full_name }}</div>
                    <div class="ir-doc-spec">{{ $row['doctor']->specialty }}</div>
                </span>
                <span class="ir-doc-stats">
                    <span>završeno: <b>{{ $row['zavrseno'] }}</b></span>
                    <span>predstojeći: {{ $row['predstojeci'] }}</span>
                    <span>nedolasci: {{ $row['nije_dosao'] }}</span>
                    <span>otkazano: {{ $row['otkazano'] }}</span>
                </span>
            </div>

            @if ($row['services'] === [])
                <div class="ir-empty">Nema završenih pregleda u izabranom periodu.</div>
            @else
                <table class="ir-table">
                    <thead>
                        <tr>
                            <th>Usluga</th>
                            <th class="ir-num" style="width: 8rem">Broj pregleda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($row['services'] as $s)
                            <tr>
                                <td>
                                    <span class="ir-bar" style="width: {{ max(6, round($s['count'] / $maxCount * 120)) }}px"></span>
                                    {{ $s['name'] }}
                                </td>
                                <td class="ir-num">{{ $s['count'] }}</td>
                            </tr>
                        @endforeach
                        <tr class="ir-total-row">
                            <td>Ukupno — {{ $row['doctor']->full_name }}</td>
                            <td class="ir-num">{{ $row['zavrseno'] }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach
</x-filament-panels::page>
