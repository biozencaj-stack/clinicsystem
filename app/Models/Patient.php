<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'jmbg', 'date_of_birth', 'gender', 'phone',
        'email', 'address', 'whatsapp_opt_in', 'whatsapp_opt_in_at', 'note',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'whatsapp_opt_in' => 'boolean',
            'whatsapp_opt_in_at' => 'datetime',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function kartonEntries(): HasMany
    {
        return $this->hasMany(KartonEntry::class)->latest('entry_date');
    }

    public function nalazi(): HasMany
    {
        return $this->hasMany(Nalaz::class)->latest('issued_at');
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class)->latest();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
