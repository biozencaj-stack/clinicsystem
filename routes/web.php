<?php

use App\Models\Doctor;
use App\Models\KartonEntry;
use App\Models\Nalaz;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

Route::get('/', fn () => redirect('/admin'));

// Tajni ICS feed po doktoru — doktor ga jednom doda u Google/Apple/Outlook kalendar.
Route::get('/kalendar/{token}.ics', function (string $token) {
    $doctor = Doctor::where('ics_token', $token)->firstOrFail();

    $events = $doctor->appointments()
        ->with(['patient', 'service'])
        ->whereIn('status', ['zakazan', 'potvrdjen', 'zahtev'])
        ->whereBetween('starts_at', [now()->subDays(30), now()->addDays(90)])
        ->get()
        ->map(function ($a) {
            // Minimizacija podataka: ime + inicijal, bez dijagnoza i detalja (ZZPL).
            $patient = $a->patient
                ? "{$a->patient->first_name} " . mb_substr($a->patient->last_name, 0, 1) . '.'
                : 'Pacijent';

            return Event::create()
                ->name("{$a->service?->name} — {$patient}")
                ->description('Detalji u internom sistemu klinike.')
                ->uniqueIdentifier("medipuls-appointment-{$a->id}")
                ->startsAt($a->starts_at)
                ->endsAt($a->ends_at ?? $a->starts_at->copy()->addMinutes(30));
        })
        ->all();

    $calendar = Calendar::create("Salus — {$doctor->full_name}")
        ->refreshInterval(15)
        ->event($events);

    return response($calendar->get(), 200, [
        'Content-Type' => 'text/calendar; charset=utf-8',
        'Content-Disposition' => 'inline; filename="salus.ics"',
    ]);
})->name('doctor.ics');

// Bezbedan link za preuzimanje nalaza — pacijent ga dobija u poruci.
Route::get('/nalaz/{token}', function (string $token) {
    $nalaz = Nalaz::where('download_token', $token)->with(['patient', 'doctor'])->firstOrFail();

    if ($nalaz->file_path && file_exists(storage_path('app/public/' . $nalaz->file_path))) {
        return response()->file(storage_path('app/public/' . $nalaz->file_path), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    return Pdf::loadView('pdf.nalaz', [
        'title' => $nalaz->title,
        'subtitle' => 'Lekarski nalaz',
        'patient' => $nalaz->patient,
        'doctor' => $nalaz->doctor,
        'date' => $nalaz->issued_at,
        'content' => $nalaz->content,
        'docNumber' => 'N-' . str_pad((string) $nalaz->id, 5, '0', STR_PAD_LEFT),
    ])->stream("nalaz-{$nalaz->id}.pdf");
})->name('nalaz.download');

// Bezbedan link za izveštaj iz kartona — pacijent ga dobija u poruci.
Route::get('/izvestaj/{token}', function (string $token) {
    $entry = KartonEntry::where('download_token', $token)->with(['patient', 'doctor'])->firstOrFail();

    $content = $entry->content;
    if ($entry->diagnosis_code) {
        $content = "Dijagnoza (MKB-10): {$entry->diagnosis_code}\n\n{$content}";
    }

    return Pdf::loadView('pdf.nalaz', [
        'title' => $entry->title,
        'subtitle' => 'Izveštaj lekara — ' . (KartonEntry::TYPES[$entry->type] ?? $entry->type),
        'patient' => $entry->patient,
        'doctor' => $entry->doctor,
        'date' => $entry->entry_date,
        'content' => $content,
        'docNumber' => 'I-' . str_pad((string) $entry->id, 5, '0', STR_PAD_LEFT),
    ])->stream("izvestaj-{$entry->id}.pdf");
})->name('izvestaj.download');

// Potvrda dolaska jednim klikom iz podsetnika.
Route::get('/termin/{token}/potvrdi', function (string $token) {
    $a = \App\Models\Appointment::where('action_token', $token)
        ->with(['doctor', 'service'])
        ->firstOrFail();

    if (in_array($a->status, ['zakazan', 'potvrdjen'])) {
        if ($a->status === 'zakazan') {
            $a->update(['status' => 'potvrdjen']);
        }

        return view('patient.action-result', [
            'icon' => '✅',
            'title' => 'Dolazak potvrđen',
            'message' => 'Hvala Vam! Vaš termin je potvrđen — vidimo se.',
            'appointment' => $a,
        ]);
    }

    return view('patient.action-result', [
        'icon' => 'ℹ️',
        'title' => 'Termin nije aktivan',
        'message' => 'Ovaj termin više nije aktivan. Ako mislite da je u pitanju greška, pozovite nas.',
    ]);
})->name('termin.potvrdi');

// Otkazivanje jednim klikom — poštuje pravilo o minimalnom roku.
Route::get('/termin/{token}/otkazi', function (string $token, \App\Services\AvailabilityService $availability) {
    $a = \App\Models\Appointment::where('action_token', $token)
        ->with(['doctor', 'service', 'patient'])
        ->firstOrFail();

    if (! in_array($a->status, \App\Models\Appointment::BLOCKING_STATUSES)) {
        return view('patient.action-result', [
            'icon' => 'ℹ️',
            'title' => 'Termin nije aktivan',
            'message' => 'Ovaj termin je već otkazan ili je prošao.',
        ]);
    }

    if (! $availability->patientCanCancel($a)) {
        return view('patient.action-result', [
            'icon' => '📞',
            'title' => 'Otkazivanje nije moguće onlajn',
            'message' => 'Do termina je manje od ' . config('clinic.min_cancel_hours')
                . ' sati, pa otkazivanje putem linka nije moguće. Molimo pozovite nas da dogovorimo novi termin.',
            'appointment' => $a,
        ]);
    }

    $a->update(['status' => 'otkazan']);

    return view('patient.action-result', [
        'icon' => '❌',
        'title' => 'Termin otkazan',
        'message' => 'Vaš termin je otkazan. Kad budete spremni, rado ćemo Vam zakazati novi.',
        'appointment' => $a,
    ]);
})->name('termin.otkazi');

// Štampa za osoblje (samo ulogovani) — brendiran PDF sa doktorom u dnu.
Route::middleware('auth')->group(function () {
    Route::get('/stampa/nalaz/{nalaz}', function (Nalaz $nalaz) {
        $nalaz->load(['patient', 'doctor']);

        return Pdf::loadView('pdf.nalaz', [
            'title' => $nalaz->title,
            'subtitle' => 'Lekarski nalaz',
            'patient' => $nalaz->patient,
            'doctor' => $nalaz->doctor,
            'date' => $nalaz->issued_at,
            'content' => $nalaz->content,
            'docNumber' => 'N-' . str_pad((string) $nalaz->id, 5, '0', STR_PAD_LEFT),
        ])->stream("nalaz-{$nalaz->id}.pdf");
    })->name('stampa.nalaz');

    Route::get('/stampa/izvestaj-rada/{from}/{to}', function (string $from, string $to) {
        abort_if(auth()->user()?->isDoctor(), 403);

        $fromDate = \Illuminate\Support\Carbon::parse($from);
        $toDate = \Illuminate\Support\Carbon::parse($to);
        $report = \App\Filament\Pages\IzvestajRada::buildReport($fromDate, $toDate);

        return Pdf::loadView('pdf.izvestaj-rada', $report + [
            'rangeLabel' => $fromDate->format('d.m.Y.') . ' — ' . $toDate->format('d.m.Y.'),
        ])->stream("izvestaj-rada-{$from}-{$to}.pdf");
    })->name('stampa.izvestaj-rada')->where(['from' => '\d{4}-\d{2}-\d{2}', 'to' => '\d{4}-\d{2}-\d{2}']);

    Route::get('/stampa/izvestaj/{kartonEntry}', function (KartonEntry $kartonEntry) {
        $kartonEntry->load(['patient', 'doctor']);

        $content = $kartonEntry->content;
        if ($kartonEntry->diagnosis_code) {
            $content = "Dijagnoza (MKB-10): {$kartonEntry->diagnosis_code}\n\n{$content}";
        }

        return Pdf::loadView('pdf.nalaz', [
            'title' => $kartonEntry->title,
            'subtitle' => 'Izveštaj lekara — ' . (KartonEntry::TYPES[$kartonEntry->type] ?? $kartonEntry->type),
            'patient' => $kartonEntry->patient,
            'doctor' => $kartonEntry->doctor,
            'date' => $kartonEntry->entry_date,
            'content' => $content,
            'docNumber' => 'I-' . str_pad((string) $kartonEntry->id, 5, '0', STR_PAD_LEFT),
        ])->stream("izvestaj-{$kartonEntry->id}.pdf");
    })->name('stampa.izvestaj');
});
