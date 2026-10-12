<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'label', 'county', 'city', 'street', 'street_number', 'postal_code', 'details',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Address $address): void {
            if ($address->equipment()->exists() || $address->appointments()->exists()) {
                throw ValidationException::withMessages([
                    'address' => 'Adresa are echipamente sau programări. Mută-le înainte să o ștergi.',
                ]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function line(): string
    {
        $place = trim(implode(', ', array_filter([
            $this->label,
            trim($this->street.' '.$this->street_number),
            $this->city,
            $this->county,
        ])));

        return $place !== '' ? $place : 'Adresă';
    }

    public function cardLine(): string
    {
        $street = trim('Str. '.$this->street.' nr. '.$this->street_number);

        return implode(', ', array_filter([$street, $this->details, $this->city, $this->county]));
    }
}
