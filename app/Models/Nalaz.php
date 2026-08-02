<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Nalaz extends Model
{
    protected $table = 'nalazs';

    protected $fillable = [
        'patient_id', 'doctor_id', 'title', 'file_path', 'issued_at',
        'download_token', 'ready_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'ready_notified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Nalaz $n) {
            $n->download_token ??= Str::random(40);
        });

        static::created(function (Nalaz $n) {
            WhatsappMessage::sendNalazReady($n);
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function downloadUrl(): string
    {
        return url("/nalaz/{$this->download_token}");
    }
}
