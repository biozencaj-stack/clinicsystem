<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #1A2629; padding: 32px 40px; }
    .header { width: 100%; border-bottom: 3px solid #0E6E6B; padding-bottom: 12px; margin-bottom: 18px; }
    .header td { vertical-align: middle; }
    .logo { font-size: 22px; font-weight: bold; color: #0E6E6B; }
    .clinic-info { text-align: right; font-size: 8.5px; color: #4A5D62; line-height: 1.5; }
    h1 { font-size: 15px; margin-bottom: 2px; }
    .subtitle { font-size: 9.5px; color: #4A5D62; margin-bottom: 14px; text-transform: capitalize; }
    .summary { width: 100%; background: #F0F6F5; border: 1px solid #DCE6E3; margin-bottom: 16px; }
    .summary td { padding: 6px 10px; }
    .summary .lbl { font-size: 7.5px; text-transform: uppercase; letter-spacing: .5px; color: #4A5D62; }
    .summary .val { font-size: 12px; font-weight: bold; }
    .doc { margin-bottom: 14px; }
    .doc-head { background: #E9F2F1; padding: 6px 8px; font-weight: bold; font-size: 10.5px; border: 1px solid #DCE6E3; }
    .doc-head .spec { font-weight: normal; color: #4A5D62; font-size: 8.5px; }
    table.services { width: 100%; border-collapse: collapse; }
    table.services th {
        text-align: left; font-size: 7.5px; text-transform: uppercase; letter-spacing: .4px;
        color: #4A5D62; padding: 4px 8px; border: 1px solid #DCE6E3; background: #F7FAF9;
    }
    table.services td { padding: 4px 8px; border: 1px solid #E5EEEC; }
    .num { text-align: right; }
    .total td { font-weight: bold; background: #F0F6F5; }
    .grand { margin-top: 6px; }
    .grand td { padding: 7px 8px; font-weight: bold; font-size: 11px; background: #0E6E6B; color: #fff; }
    .empty { padding: 5px 8px; color: #7A8C89; border: 1px solid #E5EEEC; font-size: 9px; }
    .footer { margin-top: 20px; font-size: 8px; color: #7A8C89; border-top: 1px solid #DCE6E3; padding-top: 6px; }
</style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                @if (file_exists(public_path('images/logo.png')))
                    <span class="logo"><img src="{{ public_path('images/logo.png') }}" alt="Klinika" style="height:40px"></span>
                @else
                    <span class="logo">Poliklinika Salus</span>
                @endif
            </td>
            <td class="clinic-info">
                Poliklinika Salus<br>
                Beograd &middot; salus-demo.rs<br>
                Telefon: {{ config('clinic.phone') }}
            </td>
        </tr>
    </table>

    <h1>Izveštaj rada</h1>
    <div class="subtitle">{{ $monthLabel }}</div>

    <table class="summary">
        <tr>
            <td><div class="lbl">Završenih pregleda</div><div class="val">{{ $total['zavrseno'] }}</div></td>
            <td><div class="lbl">Ukupan prihod</div><div class="val">{{ number_format($total['prihod'], 0, ',', '.') }} RSD</div></td>
            <td><div class="lbl">Nedolasci</div><div class="val">{{ $total['nije_dosao'] }}</div></td>
            <td><div class="lbl">Otkazano / odbijeno</div><div class="val">{{ $total['otkazano'] }}</div></td>
        </tr>
    </table>

    @foreach ($doctors as $row)
        <div class="doc">
            <div class="doc-head">
                {{ $row['doctor']->full_name }}
                <span class="spec">— {{ $row['doctor']->specialty }} &middot; završeno {{ $row['zavrseno'] }} &middot; nedolasci {{ $row['nije_dosao'] }} &middot; otkazano {{ $row['otkazano'] }}</span>
            </div>
            @if ($row['services'] === [])
                <div class="empty">Nema završenih pregleda u ovom mesecu.</div>
            @else
                <table class="services">
                    <thead>
                        <tr>
                            <th>Usluga</th>
                            <th class="num" style="width:60px">Broj</th>
                            <th class="num" style="width:90px">Cena</th>
                            <th class="num" style="width:100px">Ukupno</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($row['services'] as $s)
                            <tr>
                                <td>{{ $s['name'] }}</td>
                                <td class="num">{{ $s['count'] }}</td>
                                <td class="num">{{ number_format($s['price'], 0, ',', '.') }}</td>
                                <td class="num">{{ number_format($s['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="total">
                            <td>Ukupno</td>
                            <td class="num">{{ $row['zavrseno'] }}</td>
                            <td></td>
                            <td class="num">{{ number_format($row['prihod'], 0, ',', '.') }} RSD</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach

    <table class="grand" width="100%">
        <tr>
            <td>UKUPNO ZA KLINIKU — {{ $monthLabel }}</td>
            <td class="num" style="text-align:right">{{ number_format($total['prihod'], 0, ',', '.') }} RSD ({{ $total['zavrseno'] }} pregleda)</td>
        </tr>
    </table>

    <div class="footer">Izveštaj generisan {{ now()->format('d.m.Y. u H:i') }} iz internog sistema klinike. Prihod = zbir cenovnika završenih usluga u izabranom mesecu.</div>
</body>
</html>
