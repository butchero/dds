<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'user_id', 'address_id', 'billing_profile_id', 'equipment_id', 'requested_on', 'note', 'status',
    ];

    protected function casts(): array
    {
        return ['requested_on' => 'date'];
    }

    protected static function booted(): void
    {
        static::saved(function (Appointment $appointment): void {
            $becameDone = $appointment->status === 'done'
                && ($appointment->wasRecentlyCreated || $appointment->wasChanged('status'));

            if (! $becameDone || ! $appointment->equipment_id) {
                return;
            }

            $equipment = $appointment->equipment;

            if (! $equipment) {
                return;
            }

            $equipment->last_revision_on = $appointment->requested_on ?? today();
            $equipment->notified_on = null;
            $equipment->save();
        });
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            'requested' => 'Cerută',
            'confirmed' => 'Confirmată',
            'done' => 'Efectuată',
            'cancelled' => 'Anulată',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function billingProfile(): BelongsTo
    {
        return $this->belongsTo(BillingProfile::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
