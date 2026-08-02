<?php

namespace Tests\Feature;

use App\Models\Absence;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SchedulingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected AvailabilityService $availability;

    protected Carbon $monday;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->availability = app(AvailabilityService::class);
        // Ponedeljak dovoljno daleko da seedovani termini ne smetaju.
        $this->monday = today()->addWeeks(4)->startOfWeek();
    }

    public function test_radiolog_mr_pocinje_od_8_a_ostale_usluge_od_11(): void
    {
        $radiolog = Doctor::where('specialty', 'radiolog')->firstOrFail();
        $mr = Service::where('name', 'MR glave')->firstOrFail();
        $uz = Service::where('name', 'Ultrazvuk abdomena')->firstOrFail();

        $mrSlots = $this->availability->slots($radiolog, $mr, $this->monday);
        $uzSlots = $this->availability->slots($radiolog, $uz, $this->monday);

        $this->assertSame('08:00', $mrSlots[0]->format('H:i'));
        $this->assertSame('11:00', $uzSlots[0]->format('H:i'));
    }

    public function test_buffer_pravi_razmak_izmedju_mr_termina(): void
    {
        $radiolog = Doctor::where('specialty', 'radiolog')->firstOrFail();
        $mr = Service::where('name', 'MR glave')->firstOrFail(); // 45 min + 15 buffer = 60

        $slots = $this->availability->slots($radiolog, $mr, $this->monday);

        $this->assertSame('08:00', $slots[0]->format('H:i'));
        $this->assertSame('09:00', $slots[1]->format('H:i'));
    }

    public function test_zauzet_termin_nestaje_iz_ponude(): void
    {
        $kardiolog = Doctor::where('specialty', 'kardiolog')->firstOrFail();
        $pregled = Service::where('name', 'Pregled kardiologa + EKG')->firstOrFail();

        $before = collect($this->availability->slots($kardiolog, $pregled, $this->monday))
            ->map(fn ($s) => $s->format('H:i'));
        $this->assertContains('09:00', $before);

        Appointment::create([
            'patient_id' => Patient::first()->id,
            'doctor_id' => $kardiolog->id,
            'service_id' => $pregled->id,
            'starts_at' => $this->monday->copy()->setTime(9, 0),
            'status' => 'zakazan',
            'source' => 'recepcija',
        ]);

        $after = collect($this->availability->slots($kardiolog, $pregled, $this->monday))
            ->map(fn ($s) => $s->format('H:i'));
        $this->assertNotContains('09:00', $after);
    }

    public function test_odsustvo_blokira_ceo_dan(): void
    {
        $reumatolog = Doctor::where('specialty', 'reumatolog')->firstOrFail();
        $pregled = Service::where('name', 'Pregled reumatologa')->firstOrFail();

        $this->assertNotEmpty($this->availability->slots($reumatolog, $pregled, $this->monday));

        Absence::create([
            'doctor_id' => $reumatolog->id,
            'date_from' => $this->monday->toDateString(),
            'date_to' => $this->monday->toDateString(),
            'reason' => 'Test odsustvo',
        ]);

        $this->assertEmpty($this->availability->slots($reumatolog, $pregled, $this->monday));
    }

    public function test_praznik_klinike_blokira_sve_doktore(): void
    {
        Absence::create([
            'doctor_id' => null,
            'date_from' => $this->monday->toDateString(),
            'date_to' => $this->monday->toDateString(),
            'reason' => 'Praznik test',
        ]);

        foreach (Doctor::all() as $doctor) {
            $service = Service::first();
            $this->assertEmpty($this->availability->slots($doctor, $service, $this->monday));
        }
    }

    public function test_nedelja_je_neradna(): void
    {
        $kardiolog = Doctor::where('specialty', 'kardiolog')->firstOrFail();
        $pregled = Service::where('name', 'Pregled kardiologa + EKG')->firstOrFail();

        $sunday = $this->monday->copy()->addDays(6);
        $this->assertEmpty($this->availability->slots($kardiolog, $pregled, $sunday));
    }

    public function test_potvrda_linkom_menja_status(): void
    {
        $a = $this->makeAppointment(now()->addDays(3)->setTime(10, 0), 'zakazan');

        $this->get("/termin/{$a->action_token}/potvrdi")
            ->assertOk()
            ->assertSee('Dolazak potvrđen');

        $this->assertSame('potvrdjen', $a->fresh()->status);
    }

    public function test_otkazivanje_linkom_radi_van_roka_od_24h(): void
    {
        $a = $this->makeAppointment(now()->addDays(3)->setTime(10, 0), 'zakazan');

        $this->get("/termin/{$a->action_token}/otkazi")
            ->assertOk()
            ->assertSee('Termin otkazan');

        $this->assertSame('otkazan', $a->fresh()->status);
    }

    public function test_otkazivanje_linkom_blokirano_unutar_24h(): void
    {
        $a = $this->makeAppointment(now()->addHours(3), 'zakazan');

        $this->get("/termin/{$a->action_token}/otkazi")
            ->assertOk()
            ->assertSee('Otkazivanje nije moguće onlajn');

        $this->assertSame('zakazan', $a->fresh()->status);
    }

    public function test_odbijanje_zahteva_salje_poruku(): void
    {
        $patient = Patient::where('whatsapp_opt_in', true)->firstOrFail();
        $a = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => Doctor::first()->id,
            'service_id' => Service::first()->id,
            'starts_at' => now()->addDays(5)->setTime(12, 0),
            'status' => 'zahtev',
            'source' => 'sajt',
        ]);

        $a->update(['status' => 'odbijen']);
        \App\Models\Message::sendRejection($a);

        $this->assertTrue(
            $patient->messages()->where('body', 'like', '%nije dostupan%')->exists()
        );
    }

    public function test_poseban_dan_otvara_subotu_za_urologa(): void
    {
        $urolog = Doctor::where('specialty', 'urolog')->firstOrFail();
        $pregled = Service::where('name', 'Pregled urologa')->firstOrFail();

        $overrideSaturday = now()->next(Carbon::SATURDAY);
        $regularSaturday = $overrideSaturday->copy()->addWeek();

        $slots = $this->availability->slots($urolog, $pregled, $overrideSaturday);
        $this->assertNotEmpty($slots);
        $this->assertSame('10:00', $slots[0]->format('H:i'));

        $this->assertEmpty($this->availability->slots($urolog, $pregled, $regularSaturday));
    }

    public function test_sablon_za_uslugu_ima_prednost_nad_opstim(): void
    {
        $gastro = Service::where('name', 'Gastroskopija')->firstOrFail();
        $ostalo = Service::where('name', 'Pregled neurologa')->firstOrFail();

        $this->assertSame(48, \App\Models\MessageTemplate::resolve('podsetnik', $gastro->id)['offset_hours']);
        $this->assertSame(24, \App\Models\MessageTemplate::resolve('podsetnik', $ostalo->id)['offset_hours']);
    }

    public function test_gastro_podsetnik_ide_48h_ranije_sa_dijetom(): void
    {
        $gastro = Service::where('name', 'Gastroskopija')->firstOrFail();
        $appointment = Appointment::where('service_id', $gastro->id)
            ->whereIn('status', ['zakazan', 'potvrdjen'])
            ->firstOrFail();

        $reminder = \App\Models\Message::where('patient_id', $appointment->patient_id)
            ->where('type', 'podsetnik')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(
            $appointment->starts_at->copy()->subHours(48)->toDateTimeString(),
            $reminder->scheduled_for->toDateTimeString(),
        );
        $this->assertStringContainsString('dijeta', $reminder->body);
    }

    protected function makeAppointment(Carbon $when, string $status): Appointment
    {
        return Appointment::create([
            'patient_id' => Patient::first()->id,
            'doctor_id' => Doctor::first()->id,
            'service_id' => Service::first()->id,
            'starts_at' => $when,
            'status' => $status,
            'source' => 'sajt',
        ]);
    }
}
