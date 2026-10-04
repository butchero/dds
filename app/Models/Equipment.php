<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'user_id', 'name', 'type', 'last_revision_on', 'interval_months',
        'next_revision_on', 'notified_on',
    ];

    protected function casts(): array
    {
        return [
            'last_revision_on' => 'date',
            'next_revision_on' => 'date',
            'notified_on' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Equipment $equipment) {
            if ($equipment->last_revision_on && $equipment->interval_months) {
                $equipment->next_revision_on = $equipment->last_revision_on->copy()->addMonths($equipment->interval_months);
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
}
