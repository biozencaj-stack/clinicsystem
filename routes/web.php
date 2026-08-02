<?php

use App\Models\Doctor;
use App\Models\Nalaz;
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
                ->description('Detalji u internom sistemu MagnaMed.')
                ->uniqueIdentifier("magnamed-appointment-{$a->id}")
                ->startsAt($a->starts_at)
                ->endsAt($a->ends_at ?? $a->starts_at->copy()->addMinutes(30));
        })
        ->all();

    $calendar = Calendar::create("MagnaMed — {$doctor->full_name}")
        ->refreshInterval(15)
        ->event($events);

    return response($calendar->get(), 200, [
        'Content-Type' => 'text/calendar; charset=utf-8',
        'Content-Disposition' => 'inline; filename="magnamed.ics"',
    ]);
})->name('doctor.ics');

// Bezbedan link za preuzimanje nalaza — pacijent ga dobija u WhatsApp poruci.
Route::get('/nalaz/{token}', function (string $token) {
    $nalaz = Nalaz::where('download_token', $token)->with('patient')->firstOrFail();

    if ($nalaz->file_path && file_exists(storage_path('app/public/' . $nalaz->file_path))) {
        return response()->file(storage_path('app/public/' . $nalaz->file_path), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    abort(404, 'Nalaz nije dostupan.');
})->name('nalaz.download');
