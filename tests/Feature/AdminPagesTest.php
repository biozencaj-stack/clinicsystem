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

    public function test_kalendar_radi_u_sva_cetiri_prikaza(): void
    {
        $this->actingAs($this->admin);

        foreach (['dan', 'nedelja', 'mesec', 'lista'] as $mode) {
            \Livewire\Livewire::test(\App\Filament\Pages\Kalendar::class)
                ->call('setMode', $mode)
                ->assertSuccessful()
                ->call('next')
                ->assertSuccessful()
                ->call('today')
                ->assertSuccessful();
        }
    }

    public function test_klik_na_jos_u_mesecu_otvara_dan_prikaz(): void
    {
        $this->actingAs($this->admin);

        $target = now()->addDays(5)->toDateString();

        \Livewire\Livewire::test(\App\Filament\Pages\Kalendar::class)
            ->call('setMode', 'mesec')
            ->call('openDay', $target)
            ->assertSet('mode', 'dan')
            ->assertSet('anchorDate', $target)
            ->assertSuccessful();
    }

    public function test_mini_kalendar_bira_datum(): void
    {
        $this->actingAs($this->admin);

        \Livewire\Livewire::test(\App\Filament\Pages\Kalendar::class)
            ->call('pickerNext')
            ->assertSuccessful()
            ->call('goTo', now()->addMonth()->startOfMonth()->toDateString())
            ->assertSet('anchorDate', now()->addMonth()->startOfMonth()->toDateString())
            ->assertSuccessful();
    }

    public function test_filter_vise_doktora_u_kalendaru(): void
    {
        $this->actingAs($this->admin);

        $ids = Doctor::take(2)->pluck('id')->map(fn ($id) => (string) $id)->all();

        \Livewire\Livewire::test(\App\Filament\Pages\Kalendar::class)
            ->set('doctorIds', $ids)
            ->assertSuccessful()
            ->call('setMode', 'dan')
            ->assertSuccessful()
            ->call('allDoctors')
            ->assertSet('doctorIds', [])
            ->assertSuccessful();
    }

    public function test_danas_vraca_na_danasnji_datum_i_skroluje(): void
    {
        $this->actingAs($this->admin);

        \Livewire\Livewire::test(\App\Filament\Pages\Kalendar::class)
            ->call('next')
            ->call('today')
            ->assertSet('anchorDate', today()->toDateString())
            ->assertDispatched('kalendar-scroll-today');
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

    public function test_slanje_dokumenta_ide_kanalom_pacijenta(): void
    {
        $viberPatient = Patient::where('viber_opt_in', true)->where('whatsapp_opt_in', false)->firstOrFail();
        $entry = \App\Models\KartonEntry::firstOrFail();

        $message = \App\Models\Message::sendDocument($viberPatient, $entry->title, $entry->downloadUrl());

        $this->assertNotNull($message);
        $this->assertSame('viber', $message->channel);
        $this->assertStringContainsString('/izvestaj/', $message->body);

        $bezSaglasnosti = Patient::where('whatsapp_opt_in', false)
            ->where('viber_opt_in', false)
            ->where('email_opt_in', false)
            ->firstOrFail();

        $this->assertNull(\App\Models\Message::sendDocument($bezSaglasnosti, 'Test', 'http://test'));
    }

    public function test_izvestaj_preko_bezbednog_linka(): void
    {
        $entry = \App\Models\KartonEntry::firstOrFail();
        $url = $entry->downloadUrl();

        $this->get($url)
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->get('/izvestaj/nepostojeci-token')->assertNotFound();
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
