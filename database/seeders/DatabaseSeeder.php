<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\KartonEntry;
use App\Models\Nalaz;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Models\WhatsappMessage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@magnamed.rs'],
            ['name' => 'Recepcija MagnaMed', 'password' => Hash::make('magnamed2026')],
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
            ['name' => 'MR glave', 'category' => 'Radiologija', 'duration_minutes' => 45, 'price_rsd' => 21000, 'preparation' => 'Bez metalnih predmeta. Ponesite prethodne snimke.'],
            ['name' => 'MR lumbalne kičme', 'category' => 'Radiologija', 'duration_minutes' => 45, 'price_rsd' => 21000, 'preparation' => 'Bez metalnih predmeta. Ponesite prethodne snimke.'],
            ['name' => 'Ultrazvuk abdomena', 'category' => 'Radiologija', 'duration_minutes' => 30, 'price_rsd' => 6500, 'preparation' => 'Doći našte, 6h bez hrane i gaziranih pića.'],
            ['name' => 'Pregled kardiologa + EKG', 'category' => 'Kardiologija', 'duration_minutes' => 30, 'price_rsd' => 8000, 'preparation' => null],
            ['name' => 'Ultrazvuk srca (ehokardiografija)', 'category' => 'Kardiologija', 'duration_minutes' => 30, 'price_rsd' => 9000, 'preparation' => null],
            ['name' => 'Holter pritiska 24h', 'category' => 'Kardiologija', 'duration_minutes' => 20, 'price_rsd' => 6000, 'preparation' => 'Obucite komotnu majicu.'],
            ['name' => 'Pregled gastroenterologa', 'category' => 'Gastroenterologija', 'duration_minutes' => 30, 'price_rsd' => 7500, 'preparation' => null],
            ['name' => 'Gastroskopija', 'category' => 'Gastroenterologija', 'duration_minutes' => 40, 'price_rsd' => 15000, 'preparation' => 'Strogo našte 8h. Obavezna pratnja ako je sedacija.'],
            ['name' => 'Pregled endokrinologa', 'category' => 'Endokrinologija', 'duration_minutes' => 30, 'price_rsd' => 7500, 'preparation' => 'Ponesite laboratorijske analize ne starije od 30 dana.'],
            ['name' => 'Ultrazvuk štitaste žlezde', 'category' => 'Endokrinologija', 'duration_minutes' => 20, 'price_rsd' => 5500, 'preparation' => null],
            ['name' => 'Pregled reumatologa', 'category' => 'Reumatologija', 'duration_minutes' => 30, 'price_rsd' => 7500, 'preparation' => 'Ponesite prethodne nalaze i snimke.'],
            ['name' => 'Pregled neurologa', 'category' => 'Neurologija', 'duration_minutes' => 30, 'price_rsd' => 8000, 'preparation' => null],
            ['name' => 'EMNG (elektromioneurografija)', 'category' => 'Neurologija', 'duration_minutes' => 40, 'price_rsd' => 9500, 'preparation' => 'Nemojte mazati kremu na ruke i noge tog dana.'],
            ['name' => 'Pregled urologa', 'category' => 'Urologija', 'duration_minutes' => 30, 'price_rsd' => 7500, 'preparation' => null],
            ['name' => 'Ultrazvuk urotrakta', 'category' => 'Urologija', 'duration_minutes' => 25, 'price_rsd' => 6000, 'preparation' => 'Doći sa punom bešikom — 1L vode sat vremena pre pregleda.'],
        ])->map(fn ($s) => Service::create($s));

        $patientData = [
            ['Petar', 'Marković', 'M', '1968-03-14'], ['Milica', 'Lazić', 'Z', '1985-07-22'],
            ['Dragan', 'Simić', 'M', '1957-11-02'], ['Jovana', 'Kovačević', 'Z', '1992-01-30'],
            ['Zoran', 'Pavlović', 'M', '1974-06-18'], ['Katarina', 'Ristić', 'Z', '1988-09-05'],
            ['Nenad', 'Stojanović', 'M', '1963-04-27'], ['Tamara', 'Obradović', 'Z', '1996-12-11'],
            ['Vladimir', 'Živković', 'M', '1979-08-08'], ['Sanja', 'Milošević', 'Z', '1982-02-19'],
            ['Goran', 'Tomić', 'M', '1955-10-25'], ['Teodora', 'Vasić', 'Z', '2000-05-03'],
        ];

        $patients = collect($patientData)->map(function ($p, $i) {
            $optIn = $i !== 10; // jedan pacijent bez saglasnosti, da se vidi razlika
            return Patient::create([
                'first_name' => $p[0],
                'last_name' => $p[1],
                'gender' => $p[2],
                'date_of_birth' => $p[3],
                'phone' => '+3816' . (10000000 + $i * 731 + rand(100, 999)) . rand(0, 9),
                'whatsapp_opt_in' => $optIn,
                'whatsapp_opt_in_at' => $optIn ? now()->subDays(rand(5, 60)) : null,
            ]);
        });

        $svc = fn (string $name) => $services->firstWhere('name', $name);
        $doc = fn (string $spec) => $doctors->firstWhere('specialty', $spec);

        // Termini — prošla i naredna nedelja; kreiranje automatski generiše WhatsApp poruke.
        $schedule = [
            [$patients[0], 'kardiolog', 'Pregled kardiologa + EKG', now()->subDays(6)->setTime(10, 0), 'zavrsen', 'recepcija'],
            [$patients[1], 'radiolog', 'MR glave', now()->subDays(4)->setTime(12, 30), 'zavrsen', 'sajt'],
            [$patients[2], 'urolog', 'Ultrazvuk urotrakta', now()->subDays(2)->setTime(9, 15), 'zavrsen', 'telefon'],
            [$patients[3], 'endokrinolog', 'Ultrazvuk štitaste žlezde', now()->subDays(1)->setTime(14, 0), 'nije_dosao', 'recepcija'],
            [$patients[4], 'gastroenterolog', 'Gastroskopija', now()->addDays(1)->setTime(8, 30), 'potvrdjen', 'recepcija'],
            [$patients[5], 'neurolog', 'Pregled neurologa', now()->addDays(1)->setTime(11, 0), 'zakazan', 'sajt'],
            [$patients[6], 'radiolog', 'MR lumbalne kičme', now()->addDays(2)->setTime(9, 0), 'zakazan', 'telefon'],
            [$patients[7], 'kardiolog', 'Ultrazvuk srca (ehokardiografija)', now()->addDays(2)->setTime(13, 30), 'potvrdjen', 'whatsapp'],
            [$patients[8], 'reumatolog', 'Pregled reumatologa', now()->addDays(3)->setTime(10, 30), 'zakazan', 'recepcija'],
            [$patients[9], 'radiolog', 'Ultrazvuk abdomena', now()->addDays(4)->setTime(8, 45), 'zakazan', 'sajt'],
            [$patients[11], 'neurolog', 'EMNG (elektromioneurografija)', now()->addDays(5)->setTime(12, 0), 'zakazan', 'recepcija'],
            // Zahtevi koji čekaju potvrdu recepcije (badge u navigaciji + dugme "Potvrdi"):
            [$patients[10], 'urolog', 'Pregled urologa', now()->addDays(3)->setTime(15, 0), 'zahtev', 'sajt'],
            [$patients[2], 'kardiolog', 'Holter pritiska 24h', now()->addDays(6)->setTime(9, 30), 'zahtev', 'whatsapp'],
        ];

        foreach ($schedule as [$patient, $spec, $serviceName, $when, $status, $source]) {
            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doc($spec)->id,
                'service_id' => $svc($serviceName)->id,
                'starts_at' => $when,
                'status' => $status,
                'source' => $source,
            ]);
        }

        // Karton — nekoliko realnih unosa sa MKB-10 šiframa.
        $kartonData = [
            [$patients[0], 'kardiolog', 'anamneza', null, 'Bol u grudima pri naporu', "Pacijent navodi stezanje u grudima pri penjanju uz stepenice, traje 2-3 minuta, prolazi mirovanjem. Puši 20 cigareta dnevno. Otac imao infarkt u 58. godini."],
            [$patients[0], 'kardiolog', 'dijagnoza', 'I20.9', 'Angina pectoris', 'EKG: sinusni ritam, bez akutnih ishemijskih promena. Preporučena ergometrija i laboratorija (lipidni status).'],
            [$patients[0], 'kardiolog', 'terapija', null, 'Uvedena terapija', 'Bisoprolol 2,5 mg 1x1 ujutru. Kontrola za 4 nedelje sa nalazima.'],
            [$patients[1], 'radiolog', 'pregled', 'G43.9', 'MR glave — migrena', 'MR endokranijuma bez patoloških promena. Nalaz uredan. Preporučena konsultacija neurologa zbog učestalih glavobolja.'],
            [$patients[2], 'urolog', 'dijagnoza', 'N40', 'Benigna hiperplazija prostate', 'UZ: prostata uvećana, 52 ccm, rezidualni urin 40 ml. PSA u referentnim vrednostima.'],
            [$patients[3], 'endokrinolog', 'anamneza', 'E03.9', 'Kontrola štitaste žlezde', 'Pacijentkinja na terapiji levotiroksinom 50 mcg. Subjektivno bez tegoba. TSH pre 3 meseca: 2,1.'],
            [$patients[4], 'gastroenterolog', 'anamneza', 'K21.0', 'GERB — priprema za gastroskopiju', 'Gorušica i regurgitacija unazad 6 meseci, pojačano noću. IPP terapija sa delimičnim efektom. Indikovana gastroskopija.'],
        ];

        foreach ($kartonData as $i => [$patient, $spec, $type, $mkb, $title, $content]) {
            KartonEntry::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doc($spec)->id,
                'entry_date' => now()->subDays(6 - $i)->toDateString(),
                'type' => $type,
                'diagnosis_code' => $mkb,
                'title' => $title,
                'content' => $content,
            ]);
        }

        // Nalazi sa generisanim PDF-om — kreiranje automatski šalje "nalaz je spreman" poruku.
        Storage::disk('public')->makeDirectory('nalazi');
        $nalazData = [
            [$patients[0], 'kardiolog', 'EKG i kardiološki pregled', now()->subDays(5)],
            [$patients[1], 'radiolog', 'MR glave', now()->subDays(3)],
            [$patients[2], 'urolog', 'Ultrazvuk urotrakta', now()->subDays(1)],
        ];

        foreach ($nalazData as [$patient, $spec, $title, $date]) {
            $doctor = $doc($spec);
            $file = 'nalazi/nalaz-' . $patient->id . '-' . $date->format('Ymd') . '.pdf';
            Storage::disk('public')->put($file, $this->makePdf($title, $patient->full_name, $doctor->full_name, $date->format('d.m.Y.')));

            Nalaz::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'title' => $title,
                'file_path' => $file,
                'issued_at' => $date->toDateString(),
            ]);
        }

        // Primer AI bot razgovora — kako će izgledati na WhatsApp-u.
        $botPatient = $patients[7];
        $conversation = [
            ['in', 'Dobar dan, da li radite subotom i koliko kosta ultrazvuk srca?'],
            ['out', 'Dobar dan! 😊 Da, poliklinika MagnaMed radi subotom od 10 do 17h. Ultrazvuk srca (ehokardiografija) košta 9.000 RSD i traje oko 30 minuta. Da li želite da zakažete termin?'],
            ['in', 'Moze subota pre podne ako ima'],
            ['out', 'Proverila sam raspored — kod dr sci. med. Jelene Stanković (kardiolog) slobodno je u subotu u 10:30 ili 11:30. Koji termin Vam odgovara?'],
            ['in', '10:30'],
            ['out', 'Odlično! Prosledila sam zahtev recepciji na potvrdu — dobićete potvrdu u ovoj poruci čim termin bude odobren. Molim Vas ponesite prethodne kardiološke nalaze ako ih imate. Hvala Vam! 🙏'],
        ];

        foreach ($conversation as $i => [$direction, $body]) {
            WhatsappMessage::create([
                'patient_id' => $botPatient->id,
                'direction' => $direction,
                'type' => 'bot',
                'to_phone' => $direction === 'out' ? $botPatient->phone : '+381601234567',
                'body' => $body,
                'status' => 'simulirano',
                'sent_at' => now()->subHours(3)->addMinutes($i * 2),
            ]);
        }

        // Primer poruke doktoru o izmeni rasporeda (izmene istog dana idu i doktoru).
        WhatsappMessage::create([
            'doctor_id' => $doc('endokrinolog')->id,
            'direction' => 'out',
            'type' => 'izmena',
            'to_phone' => $doc('endokrinolog')->phone,
            'body' => 'Izmena rasporeda danas: pacijentkinja u 14:00 (UZ štitaste žlezde) je otkazala termin. Sledeći pacijent je u 15:30. — MagnaMed sistem',
            'status' => 'simulirano',
            'sent_at' => now()->subHours(5),
        ]);
    }

    /**
     * Minimalan validan PDF za demo nalaze (ASCII sadržaj).
     */
    private function makePdf(string $title, string $patient, string $doctor, string $date): string
    {
        $tr = fn (string $s) => strtr($s, [
            'š' => 's', 'đ' => 'dj', 'č' => 'c', 'ć' => 'c', 'ž' => 'z',
            'Š' => 'S', 'Đ' => 'Dj', 'Č' => 'C', 'Ć' => 'C', 'Ž' => 'Z',
        ]);

        $lines = [
            'POLIKLINIKA MAGNAMED — Beograd',
            '------------------------------------------',
            'NALAZ: ' . $tr($title),
            'Pacijent: ' . $tr($patient),
            'Lekar: ' . $tr($doctor),
            'Datum: ' . $date,
            '',
            'Ovo je demo dokument generisan u probnoj',
            'verziji internog sistema. U produkciji ovde',
            'stoji pravi nalaz u PDF formatu.',
        ];

        $content = "BT\n/F1 11 Tf\n50 780 Td\n14 TL\n";
        foreach ($lines as $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $content .= "({$escaped}) Tj\nT*\n";
        }
        $content .= "ET";

        $objects = [
            1 => "<< /Type /Catalog /Pages 2 0 R >>",
            2 => "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
            3 => "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>",
            4 => "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream",
            5 => "<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= "{$num} 0 obj\n{$body}\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

        return $pdf;
    }
}
