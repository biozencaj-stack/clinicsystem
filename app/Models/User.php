<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'doctor_id'])]
#[Hidden(['password', 'remember_token', 'app_authentication_secret', 'app_authentication_recovery_codes'])]
class User extends Authenticatable implements FilamentUser, \Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication, \Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery
{
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /* ——— Dvofaktorska prijava (aplikacija-autentifikator) ——— */

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret ? decrypt($this->app_authentication_secret) : null;
    }

    public function saveAppAuthenticationSecret(?string $secret): void
    {
        $this->forceFill(['app_authentication_secret' => $secret ? encrypt($secret) : null])->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes
            ? json_decode(decrypt($this->app_authentication_recovery_codes), true)
            : null;
    }

    public function saveAppAuthenticationRecoveryCodes(?array $codes): void
    {
        $this->forceFill([
            'app_authentication_recovery_codes' => $codes ? encrypt(json_encode($codes)) : null,
        ])->save();
    }

    public function doctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Doctor::class);
    }

    /** Da li je nalog vezan za doktora (ograničen pristup: svoji termini i odsustva). */
    public function isDoctor(): bool
    {
        return $this->doctor_id !== null;
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
