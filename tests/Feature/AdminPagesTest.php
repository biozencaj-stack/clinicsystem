<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Nalaz;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@magnamed.rs')->firstOrFail();
    }

    public static function adminPages(): array
    {
        return [
            ['/admin'],
            ['/admin/kalendar'],
            ['/admin/patients'],
            ['/admin/doctors'],
            ['/admin/services'],
            ['/admin/appointments'],
            ['/admin/karton-entries'],
            ['/admin/nalazs'],
            ['/admin/messages'],
            ['/admin/patients/create'],
            ['/admin/appointments/create'],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_admin_ekrani_se_otvaraju(string $path): void
    {
        $this->actingAs($this->admin)->get($path)->assertOk();
    }

    public function test_natpisi_su_u_ispravnom_padezu_i_velicini_slova(): void
    {
        $this->actingAs($this->admin)->get('/admin/services')
            ->assertSee('Dodaj uslugu')
            ->assertSee('Usluge')
            ->assertDontSee('Dodaj Usluga');

        $this->actingAs($this->admin)->get('/admin/patients')
            ->assertSee('Dodaj pacijenta')
            ->assertDontSee('Dodaj Pacijent');

        $this->actingAs($this->admin)->get('/admin/karton-entries')
            ->assertSee('Unosi u karton')
            ->assertDontSee('Unosi U Karton');

        $this->actingAs($this->admin)->get('/admin/messages')
            ->assertSee('Poruke');

        $this->actingAs($this->admin)->get('/admin/nalazs/create')
            ->assertSee('Novi nalaz')
            ->assertDontSee('Napravi Nalaz');

        $this->actingAs($this->admin)->get('/admin/doctors/create')
            ->assertSee('Novi doktor')
            ->assertDontSee('Napravi Doktor');
    }

    public function test_kartica_pacijenta_sa_kartonom(): void
    {
        $patient = Patient::first();

        $this->actingAs($this->admin)
            ->get("/admin/patients/{$patient->id}/edit")
            ->assertOk();
    }

    public function test_ics_kalendar_doktora(): void
    {
        $doctor = Doctor::first();

        $this->get("/kalendar/{$doctor->ics_token}.ics")
            ->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
    }

    public function test_nepostojeci_ics_token_vraca_404(): void
    {
        $this->get('/kalendar/nepostojeci-token.ics')->assertNotFound();
    }

    public function test_nalaz_preko_bezbednog_linka(): void
    {
        $nalaz = Nalaz::first();

        $this->get("/nalaz/{$nalaz->download_token}")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_nepostojeci_token_nalaza_vraca_404(): void
    {
        $this->get('/nalaz/nepostojeci-token')->assertNotFound();
    }

    public function test_stampa_nalaza_generise_pdf(): void
    {
        $nalaz = Nalaz::whereNotNull('content')->firstOrFail();

        $this->actingAs($this->admin)
            ->get("/stampa/nalaz/{$nalaz->id}")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_stampa_izvestaja_generise_pdf(): void
    {
        $entry = \App\Models\KartonEntry::firstOrFail();

        $this->actingAs($this->admin)
            ->get("/stampa/izvestaj/{$entry->id}")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_stampa_zahteva_prijavu(): void
    {
        $nalaz = Nalaz::firstOrFail();

        $this->get("/stampa/nalaz/{$nalaz->id}")->assertRedirect();
    }

    public function test_poruke_se_salju_po_prioritetu_kanala(): void
    {
        $this->assertGreaterThan(0, \App\Models\Message::where('channel', 'whatsapp')->count());
        $this->assertGreaterThan(0, \App\Models\Message::where('channel', 'viber')->count());
        $this->assertGreaterThan(0, \App\Models\Message::where('channel', 'email')->count());
    }

    public function test_pacijent_bez_saglasnosti_ne_dobija_poruke(): void
    {
        $patient = Patient::where('whatsapp_opt_in', false)
            ->where('viber_opt_in', false)
            ->where('email_opt_in', false)
            ->firstOrFail();

        $this->assertSame(0, $patient->messages()->count());
    }
}
