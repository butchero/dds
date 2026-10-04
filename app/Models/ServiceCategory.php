<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug', 'sort', 'show_in_sidebar'];

    protected function casts(): array
    {
        return ['show_in_sidebar' => 'boolean'];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('sort');
    }
}
