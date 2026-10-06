<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class BillingProfile extends Model
{
    protected $fillable = [
        'user_id', 'type', 'first_name', 'last_name', 'company_name', 'cui',
        'registration_number', 'is_vat_payer', 'county', 'city', 'street',
        'street_number', 'postal_code', 'details', 'bank', 'iban',
    ];

    protected function casts(): array
    {
        return [
            'is_vat_payer' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (BillingProfile $profile): void {
            if ($profile->appointments()->exists()) {
                throw ValidationException::withMessages([
                    'billing' => 'Profilul este folosit de o programare și nu poate fi șters.',
                ]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function displayName(): string
    {
        if ($this->type === 'company') {
            return $this->company_name ?: 'Firmă';
        }

        $name = trim($this->last_name.' '.$this->first_name);

        return $name !== '' ? $name : 'Persoană fizică';
    }

    public function line(): string
    {
        return trim(implode(', ', array_filter([
            $this->displayName(),
            trim($this->street.' '.$this->street_number),
            $this->city,
            $this->county,
        ])));
    }
}
