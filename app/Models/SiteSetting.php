<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['menu_position'];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], ['menu_position' => 'top']);
    }
}
