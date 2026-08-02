<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    public const EVENTS = [
        'potvrda' => 'Potvrda termina',
        'podsetnik' => 'Podsetnik pre termina',
        'nalaz' => 'Nalaz je spreman',
        'dokument' => 'Slanje dokumenta',
        'odbijen' => 'Odbijen zahtev',
    ];

    public const PLACEHOLDERS = [
        '%pacijent_ime%' => 'Ime i prezime pacijenta',
        '%usluga%' => 'Naziv usluge',
        '%datum%' => 'Datum termina (npr. 15.08.2026.)',
        '%vreme%' => 'Vreme termina (npr. 10:30)',
        '%doktor%' => 'Titula i ime doktora',
        '%priprema%' => 'Uputstvo za pripremu iz usluge',
        '%potvrdi_link%' => 'Link za potvrdu dolaska',
        '%otkazi_link%' => 'Link za otkazivanje',
        '%naziv_dokumenta%' => 'Naziv nalaza/dokumenta',
        '%dokument_link%' => 'Bezbedan link za preuzimanje',
        '%telefon_klinike%' => 'Broj telefona klinike',
    ];

    /** Podrazumevani tekstovi kad šablon nije definisan u sistemu. */
    public const DEFAULTS = [
        'potvrda' => 'Poštovani/a %pacijent_ime%, Vaš termin je zakazan: %usluga%, %datum% u %vreme% kod %doktor%. Za otkazivanje odgovorite OTKAZUJEM. — Poliklinika MagnaMed',
        'podsetnik' => 'Podsetnik: %datum% u %vreme% imate zakazan pregled (%usluga%) kod %doktor%. %priprema% ✅ Potvrdite dolazak: %potvrdi_link% ❌ Otkažite: %otkazi_link% — Poliklinika MagnaMed',
        'nalaz' => 'Poštovani/a %pacijent_ime%, Vaš nalaz „%naziv_dokumenta%“ je spreman. Preuzmite ga bezbedno na: %dokument_link% (link važi 7 dana). — Poliklinika MagnaMed',
        'dokument' => 'Poštovani/a %pacijent_ime%, dokument „%naziv_dokumenta%“ Vam je dostupan za bezbedno preuzimanje na: %dokument_link% (link važi 7 dana). — Poliklinika MagnaMed',
        'odbijen' => 'Poštovani/a %pacijent_ime%, nažalost traženi termin (%usluga%, %datum% u %vreme%) nije dostupan. Pozovite nas na %telefon_klinike% da zajedno nađemo termin koji Vam odgovara. — Poliklinika MagnaMed',
    ];

    protected $fillable = [
        'event', 'name', 'service_ids', 'offset_hours', 'body', 'active',
    ];

    protected function casts(): array
    {
        return [
            'service_ids' => 'array',
            'active' => 'boolean',
        ];
    }

    /**
     * Nalazi najbolji šablon za događaj i uslugu:
     * šablon vezan za uslugu ima prednost nad opštim; bez šablona → podrazumevani tekst.
     *
     * @return array{body: string, offset_hours: int|null}
     */
    public static function resolve(string $event, ?int $serviceId = null): array
    {
        $templates = static::query()
            ->where('event', $event)
            ->where('active', true)
            ->get();

        $specific = $serviceId
            ? $templates->first(fn ($t) => filled($t->service_ids) && in_array($serviceId, array_map('intval', $t->service_ids)))
            : null;

        $generic = $templates->first(fn ($t) => blank($t->service_ids));

        $template = $specific ?? $generic;

        return [
            'body' => $template->body ?? static::DEFAULTS[$event] ?? '',
            'offset_hours' => $template->offset_hours ?? null,
        ];
    }

    /** Zamena placeholder-a stvarnim vrednostima. */
    public static function render(string $body, array $vars): string
    {
        $body = strtr($body, $vars);

        // Počisti duple razmake ako je neki placeholder bio prazan (npr. %priprema%).
        return trim(preg_replace('/[ ]{2,}/', ' ', $body));
    }
}
