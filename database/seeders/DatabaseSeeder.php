<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\KartonEntry;
use App\Models\Message;
use App\Models\Nalaz;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@salus-demo.rs'],
            ['name' => 'Recepcija Salus', 'password' => Hash::make('salus2026')],
        );

        $doctors = collect([
            ['title' => 'prof. dr', 'name' => 'Milan Radovanović', 'specialty' => 'radiolog', 'color' => '#0E6E6B'],
            ['title' => 'dr sci. med.', 'name' => 'Jelena Stanković', 'specialty' => 'kardiolog', 'color' => '#B54A3C'],
            ['title' => 'spec. dr', 'name' => 'Nikola Petrović', 'specialty' => 'gastroenterolog', 'color' => '#B7791F'],
            ['title' => 'dr', 'name' => 'Ana Jovanović', 'specialty' => 'endokrinolog', 'color' => '#6B4FA0'],
            ['title' => 'spec. dr', 'name' => 'Marko Đorđević', 'specialty' => 'reumatolog', 'color' => '#2E7D4F'],
            ['title' => 'doc. dr', 'name' => 'Ivana Nikolić', 'specialty' => 'neurolog', 'color' => '#1F6FB7'],
            ['title' => 'spec. dr', 'name' => 'Stefan Ilić', 'specialty' => 'urolog', 'color' => '#8A6D3B'],
        ])->map(fn ($d) => Doctor::create($d + ['phone' => '+3816' . rand(10000000, 99999999)]));

        $services = collect([
            ['name' => 'MR glave', 'category' => 'Radiologija', 'duration_minutes' => 45, 'buffer_after' => 15, 'price_rsd' => 21000, 'preparation' => 'Bez metalnih predmeta. Ponesite prethodne snimke.'],
            ['name' => 'MR lumbalne kičme', 'category' => 'Radiologija', 'duration_minutes' => 45, 'buffer_after' => 15, 'price_rsd' => 21000, 'preparation' => 'Bez metalnih predmeta. Ponesite prethodne snimke.'],
            ['name' => 'Ultrazvuk abdomena', 'category' => 'Radiologija', 'duration_minutes' => 30, 'price_rsd' => 6500, 'preparation' => 'Doći našte, 6h bez hrane i gaziranih pića.'],
            ['name' => 'Pregled kardiologa + EKG', 'category' => 'Kardiologija', 'duration_minutes' => 30, 'price_rsd' => 8000, 'preparation' => null],
            ['name' => 'Ultrazvuk srca (ehokardiografija)', 'category' => 'Kardiologija', 'duration_minutes' => 30, 'price_rsd' => 9000, 'preparation' => null],
            ['name' => 'Holter pritiska 24h', 'category' => 'Kardiologija', 'duration_minutes' => 20, 'price_rsd' => 6000, 'preparation' => 'Obucite komotnu majicu.'],
            ['name' => 'Pregled gastroenterologa', 'category' => 'Gastroenterologija', 'duration_minutes' => 30, 'price_rsd' => 7500, 'preparation' => null],
            ['name' => 'Gastroskopija', 'category' => 'Gastroenterologija', 'duration_minutes' => 40, 'buffer_before' => 15, 'buffer_after' => 15, 'price_rsd' => 15000, 'preparation' => 'Strogo našte 8h. Obavezna pratnja ako je sedacija.'],
            ['name' => 'Pregled endokrinologa', 'category' => 'Endokrinologija', 'duration_minutes' => 30, 'price_rsd' => 7500, 'preparation' => 'Ponesite laboratorijske analize ne starije od 30 dana.'],
            ['name' => 'Ultrazvuk štitaste žlezde', 'category' => 'Endokrinologija', 'duration_minutes' => 20, 'price_rsd' => 5500, 'preparation' => null],
            ['name' => 'Pregled reumatologa', 'category' => 'Reumatologija', 'duration_minutes' => 30, 'price_rsd' => 7500, 'preparation' => 'Ponesite prethodne nalaze i snimke.'],
            ['name' => 'Pregled neurologa', 'category' => 'Neurologija', 'duration_minutes' => 30, 'price_rsd' => 8000, 'preparation' => null],
            ['name' => 'EMNG (elektromioneurografija)', 'category' => 'Neurologija', 'duration_minutes' => 40, 'price_rsd' => 9500, 'preparation' => 'Nemojte mazati kremu na ruke i noge tog dana.'],
            ['name' => 'Pregled urologa', 'category' => 'Urologija', 'duration_minutes' => 30, 'price_rsd' => 7500, 'preparation' => null],
            ['name' => 'Ultrazvuk urotrakta', 'category' => 'Urologija', 'duration_minutes' => 25, 'price_rsd' => 6000, 'preparation' => 'Doći sa punom bešikom — 1L vode sat vremena pre pregleda.'],
        ])->map(fn ($s) => Service::create($s));

        // ————— Pacijenti: 12 osnovnih + 20 sa istorijom poseta —————
        $firstNamesM = ['Petar', 'Dragan', 'Zoran', 'Nenad', 'Vladimir', 'Goran', 'Miloš', 'Aleksandar', 'Đorđe', 'Lazar', 'Uroš', 'Branislav', 'Slobodan', 'Dejan', 'Igor', 'Nemanja'];
        $firstNamesZ = ['Milica', 'Jovana', 'Katarina', 'Tamara', 'Sanja', 'Teodora', 'Marija', 'Ivana', 'Dragana', 'Snežana', 'Vesna', 'Ljiljana', 'Bojana', 'Nataša', 'Anđela', 'Emilija'];
        $lastNames = ['Marković', 'Lazić', 'Simić', 'Kovačević', 'Pavlović', 'Ristić', 'Stojanović', 'Obradović', 'Živković', 'Milošević', 'Tomić', 'Vasić', 'Janković', 'Petković', 'Cvetković', 'Savić', 'Popović', 'Mitrović', 'Stanić', 'Radović', 'Blagojević', 'Đukić', 'Antić', 'Filipović', 'Stevanović', 'Milenković', 'Todorović', 'Aleksić', 'Zdravković', 'Gajić', 'Perić', 'Kostić'];

        $patients = collect();
        foreach (range(0, 31) as $i) {
            $isMale = $i % 2 === 0;
            $first = $isMale ? $firstNamesM[$i % 16] : $firstNamesZ[$i % 16];
            $last = $lastNames[$i];

            // Kanali obaveštenja: većina WhatsApp, deo Viber, deo e-mail, poneko bez saglasnosti.
            [$wa, $vb, $em] = match (true) {
                in_array($i, [5, 9, 14, 21, 27]) => [false, true, false],  // Viber korisnici
                in_array($i, [2, 17, 24, 30]) => [false, false, true],     // samo e-mail
                in_array($i, [10, 26]) => [false, false, false],           // bez saglasnosti
                default => [true, false, false],
            };

            $patients->push(Patient::create([
                'first_name' => $first,
                'last_name' => $last,
                'gender' => $isMale ? 'M' : 'Z',
                'date_of_birth' => now()->subYears(25 + ($i * 7) % 50)->subDays($i * 11)->toDateString(),
                'phone' => '+38164' . str_pad((string) (1000000 + $i * 2741), 7, '0', STR_PAD_LEFT),
                'email' => Str::ascii(mb_strtolower("{$first}.{$last}")) . '@example.com',
                'whatsapp_opt_in' => $wa,
                'whatsapp_opt_in_at' => $wa ? now()->subDays(rand(5, 90)) : null,
                'viber_opt_in' => $vb,
                'email_opt_in' => $em,
            ]));
        }

        // Parovi specijalnost → [pregled, dodatna usluga] za istoriju poseta.
        $visitPlans = [
            'kardiolog' => ['Pregled kardiologa + EKG', 'Ultrazvuk srca (ehokardiografija)', 'Holter pritiska 24h'],
            'radiolog' => ['Ultrazvuk abdomena', 'MR glave', 'MR lumbalne kičme'],
            'gastroenterolog' => ['Pregled gastroenterologa', 'Gastroskopija'],
            'endokrinolog' => ['Pregled endokrinologa', 'Ultrazvuk štitaste žlezde'],
            'reumatolog' => ['Pregled reumatologa'],
            'neurolog' => ['Pregled neurologa', 'EMNG (elektromioneurografija)'],
            'urolog' => ['Pregled urologa', 'Ultrazvuk urotrakta'],
        ];

        $kartonTemplates = [
            'kardiolog' => [
                ['anamneza', 'I10', 'Kontrola krvnog pritiska', 'Pacijent na terapiji, subjektivno bez tegoba. TA u kućnim merenjima 135/85.'],
                ['pregled', 'I10', 'Kardiološki pregled', 'EKG: sinusni ritam, f ~72/min. Auskultatorno b.o. Nastaviti postojeću terapiju.'],
                ['kontrola', 'I10', 'Kontrolni pregled', 'Vrednosti pritiska stabilne. Kontrola za 6 meseci sa lipidnim statusom.'],
            ],
            'radiolog' => [
                ['pregled', null, 'Ultrazvučni pregled', 'Jetra homogene ehostrukture, normalne veličine. Žučna kesa bez kalkulusa. Pankreas i slezina b.o.'],
                ['pregled', 'M54.5', 'MR pregled', 'Degenerativne promene L4-L5 sa protruzijom diska bez kompresije korena. Preporučena fizikalna terapija.'],
            ],
            'gastroenterolog' => [
                ['anamneza', 'K21.0', 'Tegobe sa varenjem', 'Gorušica unazad nekoliko meseci, pojačano posle obroka. Propisana IPP terapija.'],
                ['kontrola', 'K21.0', 'Kontrola terapije', 'Tegobe značajno smanjene. Nastaviti terapiju još 4 nedelje pa postepeno ukidanje.'],
            ],
            'endokrinolog' => [
                ['anamneza', 'E03.9', 'Kontrola funkcije štitaste žlezde', 'Na supstitucionoj terapiji. TSH u referentnom opsegu. Bez subjektivnih tegoba.'],
                ['kontrola', 'E11.9', 'Kontrola glikemije', 'HbA1c 6,8% — zadovoljavajuća regulacija. Nastaviti terapiju i režim ishrane.'],
            ],
            'reumatolog' => [
                ['anamneza', 'M15.9', 'Bolovi u zglobovima', 'Jutarnja ukočenost šaka do 30 min. Laboratorija: RF negativan, CRP blago povišen.'],
                ['kontrola', 'M15.9', 'Kontrolni pregled', 'Tegobe u remisiji uz terapiju. Preporučena kontrola za 3 meseca.'],
            ],
            'neurolog' => [
                ['anamneza', 'G43.9', 'Glavobolje', 'Učestale glavobolje pulsirajućeg karaktera, 3-4x mesečno. Neurološki nalaz uredan.'],
                ['kontrola', 'G43.9', 'Kontrola terapije', 'Učestalost napada smanjena na 1x mesečno. Nastaviti profilaktičku terapiju.'],
            ],
            'urolog' => [
                ['pregled', 'N40', 'Urološki pregled', 'UZ: prostata blago uvećana. PSA u referentnim vrednostima. Kontrola za godinu dana.'],
                ['kontrola', 'N20.0', 'Kontrola posle kalkuloze', 'Bez recidiva kalkulusa. Preporučen povećan unos tečnosti.'],
            ],
        ];

        $svc = fn (string $name) => $services->firstWhere('name', $name);
        $doc = fn (string $spec) => $doctors->firstWhere('specialty', $spec);
        $specialties = array_keys($visitPlans);

        // ————— Radno vreme doktora —————
        $mrIds = [$svc('MR glave')->id, $svc('MR lumbalne kičme')->id];
        foreach ($doctors as $doctor) {
            foreach (range(1, 5) as $weekday) {
                if ($doctor->specialty === 'radiolog') {
                    // Radiolog: MR samo pre podne, posle podne sve usluge.
                    \App\Models\DoctorWorkingHour::create(['doctor_id' => $doctor->id, 'weekday' => $weekday, 'starts_at' => '08:00', 'ends_at' => '11:00', 'service_ids' => $mrIds]);
                    \App\Models\DoctorWorkingHour::create(['doctor_id' => $doctor->id, 'weekday' => $weekday, 'starts_at' => '11:00', 'ends_at' => '15:00', 'service_ids' => null]);
                } else {
                    \App\Models\DoctorWorkingHour::create(['doctor_id' => $doctor->id, 'weekday' => $weekday, 'starts_at' => '08:00', 'ends_at' => '15:00', 'service_ids' => null]);
                }
            }
        }
        // Kardiolog radi i subotom (poklapa se sa primerom bot razgovora).
        \App\Models\DoctorWorkingHour::create(['doctor_id' => $doc('kardiolog')->id, 'weekday' => 6, 'starts_at' => '10:00', 'ends_at' => '17:00', 'service_ids' => null]);

        // ————— Odsustva i praznici —————
        \App\Models\Absence::create(['doctor_id' => null, 'date_from' => now()->year . '-11-11', 'date_to' => now()->year . '-11-11', 'reason' => 'Dan primirja (državni praznik)', 'repeat_yearly' => true]);
        \App\Models\Absence::create(['doctor_id' => $doc('reumatolog')->id, 'date_from' => now()->addDays(10)->toDateString(), 'date_to' => now()->addDays(14)->toDateString(), 'reason' => 'Godišnji odmor', 'repeat_yearly' => false]);

        // ————— Poseban dan: urolog radi sledeću subotu (zamena smene) —————
        \App\Models\DoctorScheduleOverride::create([
            'doctor_id' => $doc('urolog')->id,
            'date' => now()->next(\Illuminate\Support\Carbon::SATURDAY)->toDateString(),
            'reason' => 'Subotnji rad — zamena smene',
            'periods' => [['starts_at' => '10:00', 'ends_at' => '14:00', 'service_ids' => null]],
        ]);

        // ————— Šabloni poruka: podrazumevani + primer za gastroskopiju (48h, dijeta) —————
        foreach (\App\Models\MessageTemplate::DEFAULTS as $event => $body) {
            \App\Models\MessageTemplate::create([
                'event' => $event,
                'name' => \App\Models\MessageTemplate::EVENTS[$event] . ' (standardni)',
                'service_ids' => null,
                'offset_hours' => $event === 'podsetnik' ? 24 : null,
                'body' => $body,
            ]);
        }

        \App\Models\MessageTemplate::create([
            'event' => 'podsetnik',
            'name' => 'Podsetnik za gastroskopiju sa dijetom (48h)',
            'service_ids' => [$svc('Gastroskopija')->id],
            'offset_hours' => 48,
            'body' => 'Poštovani/a %pacijent_ime%, %datum% u %vreme% imate zakazanu gastroskopiju kod %doktor%. VAŽNO: 48h pre pregleda lagana dijeta bez teške hrane; poslednji obrok najkasnije 8h pre pregleda, posle toga strogo ništa. Obavezna pratnja ako je sedacija. ✅ Potvrdite: %potvrdi_link% ❌ Otkažite: %otkazi_link% — Poliklinika Salus',
        ]);

        // ————— Doktorski nalog (dr Jelena Stanković se sama uloguje) —————
        User::firstOrCreate(
            ['email' => 'doktor@salus-demo.rs'],
            [
                'name' => 'dr sci. med. Jelena Stanković',
                'password' => Hash::make('doktor2026'),
                'doctor_id' => $doc('kardiolog')->id,
            ],
        );

        // ————— Istorija: pacijenti 12-31 imaju 2-4 završene posete sa unosima u karton —————
        foreach ($patients->slice(12) as $i => $patient) {
            $spec = $specialties[$i % count($specialties)];
            $doctor = $doc($spec);
            $visitCount = 2 + ($i % 3); // 2, 3 ili 4 posete
            $planServices = $visitPlans[$spec];
            $templates = $kartonTemplates[$spec];

            foreach (range(1, $visitCount) as $v) {
                $when = now()->subMonths($visitCount - $v + 1)->subDays(($i * 3 + $v * 5) % 20)
                    ->setTime(9 + (($i + $v) % 8), [0, 15, 30, 45][($i + $v) % 4]);
                $serviceName = $planServices[($v - 1) % count($planServices)];

                $appointment = Appointment::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'service_id' => $svc($serviceName)->id,
                    'starts_at' => $when,
                    'status' => 'zavrsen',
                    'source' => ['recepcija', 'telefon', 'sajt'][($i + $v) % 3],
                ]);

                $tpl = $templates[($v - 1) % count($templates)];
                KartonEntry::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_id' => $appointment->id,
                    'entry_date' => $when->toDateString(),
                    'type' => $tpl[0],
                    'diagnosis_code' => $tpl[1],
                    'title' => $tpl[2],
                    'content' => $tpl[3],
                ]);
            }

            // Svaki treći pacijent sa istorijom ima i zakazanu kontrolu ubuduće.
            if ($i % 3 === 0) {
                Appointment::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'service_id' => $svc($planServices[0])->id,
                    'starts_at' => now()->addDays(3 + ($i % 10))->setTime(10 + ($i % 6), 0),
                    'status' => 'zakazan',
                    'source' => 'recepcija',
                ]);
            }
        }

        // ————— Termini tekuće nedelje za prvih 12 pacijenata —————
        $schedule = [
            [0, 'kardiolog', 'Pregled kardiologa + EKG', now()->subDays(6)->setTime(10, 0), 'zavrsen', 'recepcija'],
            [1, 'radiolog', 'MR glave', now()->subDays(4)->setTime(12, 30), 'zavrsen', 'sajt'],
            [2, 'urolog', 'Ultrazvuk urotrakta', now()->subDays(2)->setTime(9, 15), 'zavrsen', 'telefon'],
            [3, 'endokrinolog', 'Ultrazvuk štitaste žlezde', now()->subDays(1)->setTime(14, 0), 'nije_dosao', 'recepcija'],
            [4, 'gastroenterolog', 'Gastroskopija', now()->addDays(1)->setTime(8, 30), 'potvrdjen', 'recepcija'],
            [5, 'neurolog', 'Pregled neurologa', now()->addDays(1)->setTime(11, 0), 'zakazan', 'sajt'],
            [6, 'radiolog', 'MR lumbalne kičme', now()->addDays(2)->setTime(9, 0), 'zakazan', 'telefon'],
            [7, 'kardiolog', 'Ultrazvuk srca (ehokardiografija)', now()->addDays(2)->setTime(13, 30), 'potvrdjen', 'whatsapp'],
            [8, 'reumatolog', 'Pregled reumatologa', now()->addDays(3)->setTime(10, 30), 'zakazan', 'recepcija'],
            [9, 'radiolog', 'Ultrazvuk abdomena', now()->addDays(4)->setTime(8, 45), 'zakazan', 'sajt'],
            [11, 'neurolog', 'EMNG (elektromioneurografija)', now()->addDays(5)->setTime(12, 0), 'zakazan', 'recepcija'],
            [10, 'urolog', 'Pregled urologa', now()->addDays(3)->setTime(15, 0), 'zahtev', 'sajt'],
            [2, 'kardiolog', 'Holter pritiska 24h', now()->addDays(6)->setTime(9, 30), 'zahtev', 'whatsapp'],
        ];

        foreach ($schedule as [$pi, $spec, $serviceName, $when, $status, $source]) {
            Appointment::create([
                'patient_id' => $patients[$pi]->id,
                'doctor_id' => $doc($spec)->id,
                'service_id' => $svc($serviceName)->id,
                'starts_at' => $when,
                'status' => $status,
                'source' => $source,
            ]);
        }

        // ————— No-show primeri: pacijent sa 2 i pacijent sa 3 nedolaska —————
        foreach ([[3, 2], [10, 3]] as [$pi, $count]) {
            foreach (range(1, $count - ($pi === 3 ? 1 : 0)) as $n) {
                Appointment::create([
                    'patient_id' => $patients[$pi]->id,
                    'doctor_id' => $doctors[$n % 7]->id,
                    'service_id' => $services[$n % 15]->id,
                    'starts_at' => now()->subMonths($n + 1)->setTime(10, 0),
                    'status' => 'nije_dosao',
                    'source' => 'recepcija',
                ]);
            }
        }

        // ————— Karton prvih pacijenata (detaljan primer) —————
        $kartonData = [
            [0, 'kardiolog', 'anamneza', null, 'Bol u grudima pri naporu', "Pacijent navodi stezanje u grudima pri penjanju uz stepenice, traje 2-3 minuta, prolazi mirovanjem. Puši 20 cigareta dnevno. Otac imao infarkt u 58. godini."],
            [0, 'kardiolog', 'dijagnoza', 'I20.9', 'Angina pectoris', 'EKG: sinusni ritam, bez akutnih ishemijskih promena. Preporučena ergometrija i laboratorija (lipidni status).'],
            [0, 'kardiolog', 'terapija', null, 'Uvedena terapija', 'Bisoprolol 2,5 mg 1x1 ujutru. Kontrola za 4 nedelje sa nalazima.'],
            [1, 'radiolog', 'pregled', 'G43.9', 'MR glave — migrena', 'MR endokranijuma bez patoloških promena. Nalaz uredan. Preporučena konsultacija neurologa zbog učestalih glavobolja.'],
            [2, 'urolog', 'dijagnoza', 'N40', 'Benigna hiperplazija prostate', 'UZ: prostata uvećana, 52 ccm, rezidualni urin 40 ml. PSA u referentnim vrednostima.'],
            [3, 'endokrinolog', 'anamneza', 'E03.9', 'Kontrola štitaste žlezde', 'Pacijentkinja na terapiji levotiroksinom 50 mcg. Subjektivno bez tegoba. TSH pre 3 meseca: 2,1.'],
            [4, 'gastroenterolog', 'anamneza', 'K21.0', 'GERB — priprema za gastroskopiju', 'Gorušica i regurgitacija unazad 6 meseci, pojačano noću. IPP terapija sa delimičnim efektom. Indikovana gastroskopija.'],
        ];

        foreach ($kartonData as $i => [$pi, $spec, $type, $mkb, $title, $content]) {
            KartonEntry::create([
                'patient_id' => $patients[$pi]->id,
                'doctor_id' => $doc($spec)->id,
                'entry_date' => now()->subDays(6 - $i)->toDateString(),
                'type' => $type,
                'diagnosis_code' => $mkb,
                'title' => $title,
                'content' => $content,
            ]);
        }

        // ————— Nalazi sa sadržajem (za brendiranu štampu) — poruka ide automatski —————
        Storage::disk('public')->makeDirectory('nalazi');
        $nalazData = [
            [0, 'kardiolog', 'EKG i kardiološki pregled', now()->subDays(5), "EKG: sinusni ritam, frekvencija 72/min, bez ishemijskih promena.\n\nAuskultacija: srčani tonovi jasni, bez šumova. TA 130/85 mmHg.\n\nZaključak: Nalaz u granicama normale za uzrast. Preporučena ergometrija u sklopu dalje obrade i kontrola za 4 nedelje sa lipidnim statusom."],
            [1, 'radiolog', 'MR glave', now()->subDays(3), "MR endokranijuma u T1, T2 i FLAIR sekvencama:\n\nMoždani parenhim urednog signala, bez fokalnih lezija. Komorni sistem urednog položaja i širine. Kortikalni sulkusi primereni uzrastu. Paranazalni sinusi uredno pneumatizovani.\n\nZaključak: Uredan MR nalaz endokranijuma."],
            [2, 'urolog', 'Ultrazvuk urotrakta', now()->subDays(1), "Oba bubrega normalne veličine i položaja, parenhim očuvane širine, bez kalkulusa i dilatacije kanalnog sistema.\n\nMokraćna bešika glatkih zidova. Prostata dijametra 52 ccm, homogene strukture, rezidualni urin 40 ml.\n\nZaključak: Benigno uvećanje prostate. Preporučena kontrola sa PSA za 12 meseci."],
        ];

        foreach ($nalazData as [$pi, $spec, $title, $date, $content]) {
            Nalaz::create([
                'patient_id' => $patients[$pi]->id,
                'doctor_id' => $doc($spec)->id,
                'title' => $title,
                'content' => $content,
                'issued_at' => $date->toDateString(),
            ]);
        }

        // ————— Primer AI bot razgovora —————
        $botPatient = $patients[7];
        $conversation = [
            ['in', 'Dobar dan, da li radite subotom i koliko kosta ultrazvuk srca?'],
            ['out', 'Dobar dan! 😊 Da, poliklinika Salus radi subotom od 10 do 17h. Ultrazvuk srca (ehokardiografija) košta 9.000 RSD i traje oko 30 minuta. Da li želite da zakažete termin?'],
            ['in', 'Moze subota pre podne ako ima'],
            ['out', 'Proverila sam raspored — kod dr sci. med. Jelene Stanković (kardiolog) slobodno je u subotu u 10:30 ili 11:30. Koji termin Vam odgovara?'],
            ['in', '10:30'],
            ['out', 'Odlično! Prosledila sam zahtev recepciji na potvrdu — dobićete potvrdu u ovoj poruci čim termin bude odobren. Molim Vas ponesite prethodne kardiološke nalaze ako ih imate. Hvala Vam! 🙏'],
        ];

        foreach ($conversation as $i => [$direction, $body]) {
            Message::create([
                'patient_id' => $botPatient->id,
                'direction' => $direction,
                'channel' => 'whatsapp',
                'type' => 'bot',
                'destination' => $direction === 'out' ? $botPatient->phone : '+381601234567',
                'body' => $body,
                'status' => 'simulirano',
                'sent_at' => now()->subHours(3)->addMinutes($i * 2),
            ]);
        }

        // ————— Poruka doktoru o izmeni rasporeda —————
        Message::create([
            'doctor_id' => $doc('endokrinolog')->id,
            'direction' => 'out',
            'channel' => 'whatsapp',
            'type' => 'izmena',
            'destination' => $doc('endokrinolog')->phone,
            'body' => 'Izmena rasporeda danas: pacijentkinja u 14:00 (UZ štitaste žlezde) je otkazala termin. Sledeći pacijent je u 15:30. — interni sistem klinike',
            'status' => 'simulirano',
            'sent_at' => now()->subHours(5),
        ]);
    }
}
