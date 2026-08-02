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
            ['/admin/patients'],
            ['/admin/doctors'],
            ['/admin/services'],
            ['/admin/appointments'],
            ['/admin/karton-entries'],
            ['/admin/nalazs'],
            ['/admin/whatsapp-messages'],
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

        $this->actingAs($this->admin)->get('/admin/whatsapp-messages')
            ->assertSee('WhatsApp poruke')
            ->assertDontSee('WhatsApp Poruke');

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
}
