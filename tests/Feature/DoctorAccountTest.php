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

    public function test_doktor_ne_vidi_pacijente_usluge_ni_poruke(): void
    {
        $this->actingAs($this->doctorUser);

        $this->get('/admin/patients')->assertForbidden();
        $this->get('/admin/services')->assertForbidden();
        $this->get('/admin/messages')->assertForbidden();
        $this->get('/admin/doctors')->assertForbidden();
        $this->get('/admin/nalazs')->assertForbidden();
        $this->get('/admin/karton-entries')->assertForbidden();
    }

    public function test_doktor_vidi_kalendar_termine_i_odsustva(): void
    {
        $this->actingAs($this->doctorUser);

        $this->get('/admin')->assertOk();
        $this->get('/admin/kalendar')->assertOk();
        $this->get('/admin/appointments')->assertOk();
        $this->get('/admin/absences')->assertOk();
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

    public function test_recepcija_i_dalje_vidi_sve(): void
    {
        $admin = User::where('email', 'admin@magnamed.rs')->firstOrFail();
        $this->actingAs($admin);

        $this->get('/admin/patients')->assertOk();
        $this->assertSame(Appointment::count(), AppointmentResource::getEloquentQuery()->count());
    }
}
