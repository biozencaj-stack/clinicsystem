<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #1A2629; padding: 36px 44px; }
    .header { width: 100%; border-bottom: 3px solid #0E6E6B; padding-bottom: 14px; margin-bottom: 22px; }
    .header td { vertical-align: middle; }
    .logo { font-size: 26px; font-weight: bold; color: #0E6E6B; letter-spacing: 0.5px; }
    .logo img { height: 46px; }
    .clinic-info { text-align: right; font-size: 9px; color: #4A5D62; line-height: 1.55; }
    .doc-title { font-size: 17px; font-weight: bold; margin: 4px 0 2px; }
    .doc-subtitle { font-size: 10px; color: #4A5D62; margin-bottom: 16px; }
    .meta { width: 100%; background: #F0F6F5; border: 1px solid #DCE6E3; margin-bottom: 20px; }
    .meta td { padding: 7px 12px; font-size: 10px; }
    .meta .lbl { color: #4A5D62; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.5px; }
    .meta .val { font-weight: bold; font-size: 11px; }
    .content { line-height: 1.7; min-height: 300px; white-space: pre-wrap; }
    .footer { margin-top: 46px; width: 100%; }
    .footer td { vertical-align: bottom; }
    .sig { text-align: center; width: 240px; }
    .sig .line { border-top: 1px solid #1A2629; margin-top: 44px; padding-top: 6px; font-size: 10px; }
    .sig .doctor { font-weight: bold; font-size: 11px; }
    .sig .spec { font-size: 9px; color: #4A5D62; }
    .print-date { font-size: 8.5px; color: #4A5D62; }
    .disclaimer { margin-top: 24px; padding-top: 8px; border-top: 1px solid #DCE6E3; font-size: 8px; color: #97ACA8; text-align: center; }
</style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                @if (file_exists(public_path('images/logo.png')))
                    <span class="logo"><img src="{{ public_path('images/logo.png') }}" alt="Salus"></span>
                @else
                    <span class="logo">Poliklinika Salus</span>
                @endif
            </td>
            <td class="clinic-info">
                Poliklinika Salus<br>
                Beograd &middot; salus-demo.rs<br>
                Telefon: +381 66 123 456 &middot; office@salus-demo.rs
            </td>
        </tr>
    </table>

    <div class="doc-title">{{ $title }}</div>
    <div class="doc-subtitle">{{ $subtitle }}</div>

    <table class="meta">
        <tr>
            <td>
                <div class="lbl">Pacijent</div>
                <div class="val">{{ $patient->full_name }}</div>
            </td>
            <td>
                <div class="lbl">Datum rođenja</div>
                <div class="val">{{ $patient->date_of_birth?->format('d.m.Y.') ?? '—' }}</div>
            </td>
            <td>
                <div class="lbl">Datum</div>
                <div class="val">{{ $date->format('d.m.Y.') }}</div>
            </td>
            <td>
                <div class="lbl">Broj dokumenta</div>
                <div class="val">{{ $docNumber }}</div>
            </td>
        </tr>
    </table>

    <div class="content">{{ $content ?: 'Sadržaj nalaza nije unet u sistem.' }}</div>

    <table class="footer">
        <tr>
            <td class="print-date">Dokument generisan {{ now()->format('d.m.Y. u H:i') }} iz internog sistema poliklinike Salus.</td>
            <td class="sig">
                <div class="line">
                    @if ($doctor)
                        <div class="doctor">{{ $doctor->full_name }}</div>
                        <div class="spec">spec. {{ $doctor->specialty }} &middot; potpis i faksimil</div>
                    @else
                        <div class="spec">potpis i faksimil lekara</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="disclaimer">Ovaj dokument je medicinska dokumentacija poliklinike Salus i namenjen je isključivo pacijentu na koga se odnosi.</div>
</body>
</html>
