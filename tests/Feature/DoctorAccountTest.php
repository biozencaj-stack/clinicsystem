<?php

namespace Tests\Feature;

use App\Filament\Resources\Absences\Pages\CreateAbsence;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Absence;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DoctorAccountTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctorUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->doctorUser = User::where('email', 'doktor@magnamed.rs')->firstOrFail();
    }

    public function test_doktor_ne_vidi_administrativne_module(): void
    {
        $this->actingAs($this->doctorUser);

        $this->get('/admin/services')->assertForbidden();
        $this->get('/admin/messages')->assertForbidden();
        $this->get('/admin/doctors')->assertForbidden();
        $this->get('/admin/message-templates')->assertForbidden();
    }

    public function test_doktor_vidi_kalendar_termine_odsustva_i_pacijente(): void
    {
        $this->actingAs($this->doctorUser);

        $this->get('/admin')->assertOk();
        $this->get('/admin/kalendar')->assertOk();
        $this->get('/admin/appointments')->assertOk();
        $this->get('/admin/absences')->assertOk();
        $this->get('/admin/patients')->assertOk();
        $this->get('/admin/karton-entries')->assertOk();
        $this->get('/admin/nalazs')->assertOk();
    }

    public function test_doktor_vidi_samo_svoje_pacijente_i_moze_da_otvori_karton(): void
    {
        $this->actingAs($this->doctorUser);

        $visiblePatients = \App\Filament\Resources\Patients\PatientResource::getEloquentQuery()->get();
        $this->assertNotEmpty($visiblePatients);
        $this->assertLessThan(\App\Models\Patient::count(), $visiblePatients->count());

        // Svaki vidljivi pacijent ima vezu sa ovim doktorom.
        $doctorId = $this->doctorUser->doctor_id;
        foreach ($visiblePatients as $patient) {
            $treated = $patient->appointments()->where('doctor_id', $doctorId)->exists()
                || $patient->kartonEntries()->where('doctor_id', $doctorId)->exists()
                || $patient->nalazi()->where('doctor_id', $doctorId)->exists();
            $this->assertTrue($treated, "Pacijent {$patient->full_name} nije lečen kod ovog doktora");
        }

        // Karton svog pacijenta može da otvori.
        $this->get('/admin/patients/' . $visiblePatients->first()->id . '/edit')->assertOk();

        // Tuđeg pacijenta ne može.
        $other = \App\Models\Patient::whereNotIn('id', $visiblePatients->pluck('id'))->firstOrFail();
        $this->get('/admin/patients/' . $other->id . '/edit')->assertNotFound();
    }

    public function test_doktor_u_globalnim_listama_vidi_samo_svoje_unose(): void
    {
        $this->actingAs($this->doctorUser);

        $doctorId = $this->doctorUser->doctor_id;

        $this->assertSame(
            \App\Models\KartonEntry::where('doctor_id', $doctorId)->count(),
            \App\Filament\Resources\KartonEntries\KartonEntryResource::getEloquentQuery()->count(),
        );
        $this->assertSame(
            \App\Models\Nalaz::where('doctor_id', $doctorId)->count(),
            \App\Filament\Resources\Nalazs\NalazResource::getEloquentQuery()->count(),
        );
    }

    public function test_doktor_vidi_samo_svoje_termine(): void
    {
        $this->actingAs($this->doctorUser);

        $visible = AppointmentResource::getEloquentQuery()->count();
        $own = Appointment::where('doctor_id', $this->doctorUser->doctor_id)->count();
        $all = Appointment::count();

        $this->assertSame($own, $visible);
        $this->assertLessThan($all, $visible);
    }

    public function test_doktor_sam_unosi_svoje_odsustvo(): void
    {
        $this->actingAs($this->doctorUser);

        Livewire::test(CreateAbsence::class)
            ->fillForm([
                'reason' => 'Stručni kongres',
                'date_from' => now()->addDays(20)->toDateString(),
                'date_to' => now()->addDays(22)->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $absence = Absence::where('reason', 'Stručni kongres')->firstOrFail();
        $this->assertSame($this->doctorUser->doctor_id, $absence->doctor_id);
    }

    public function test_globalna_pretraga_radi_i_postuje_doktorski_pristup(): void
    {
        $admin = User::where('email', 'admin@magnamed.rs')->firstOrFail();

        // Recepcija: pretraga nalazi pacijente po prezimenu i telefonu.
        $this->actingAs($admin);
        $patient = \App\Models\Patient::firstOrFail();

        $byName = \App\Filament\Resources\Patients\PatientResource::getGlobalSearchResults($patient->last_name);
        $this->assertTrue($byName->contains(fn ($r) => str_contains($r->title, $patient->last_name)));

        $byPhone = \App\Filament\Resources\Patients\PatientResource::getGlobalSearchResults(substr($patient->phone, -6));
        $this->assertNotEmpty($byPhone);

        // Termini se pretražuju po imenu pacijenta.
        $appointments = \App\Filament\Resources\Appointments\AppointmentResource::getGlobalSearchResults($patient->last_name);
        $this->assertNotEmpty($appointments);

        // Doktor: pretraga vraća samo pacijente koje je lečio.
        $this->actingAs($this->doctorUser);
        $doctorId = $this->doctorUser->doctor_id;

        $tudji = \App\Models\Patient::whereDoesntHave('appointments', fn ($q) => $q->where('doctor_id', $doctorId))
            ->whereDoesntHave('kartonEntries', fn ($q) => $q->where('doctor_id', $doctorId))
            ->whereDoesntHave('nalazi', fn ($q) => $q->where('doctor_id', $doctorId))
            ->firstOrFail();

        $results = \App\Filament\Resources\Patients\PatientResource::getGlobalSearchResults($tudji->last_name);
        $this->assertFalse(
            $results->contains(fn ($r) => str_contains($r->title, $tudji->full_name)),
            'Doktor u pretrazi ne sme videti tuđe pacijente'
        );
    }

    public function test_recepcija_i_dalje_vidi_sve(): void
    {
        $admin = User::where('email', 'admin@magnamed.rs')->firstOrFail();
        $this->actingAs($admin);

        $this->get('/admin/patients')->assertOk();
        $this->assertSame(Appointment::count(), AppointmentResource::getEloquentQuery()->count());
    }
}
