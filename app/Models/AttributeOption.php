<?php

namespace App\Models;

use Database\Factories\AttributeOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class AttributeOption extends Model
{
    /** @use HasFactory<AttributeOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'attribute_id', 'label', 'slug', 'sort',
    ];

    protected $attributes = [
        'sort' => 0,
    ];

    protected static function booted(): void
    {
        static::saving(function (AttributeOption $option): void {
            if (filled($option->slug)) {
                return;
            }

            $base = Str::slug((string) $option->label) ?: 'optiune';
            $slug = $base;
            $suffix = 2;

            while (
                static::query()
                    ->where('attribute_id', $option->attribute_id)
                    ->where('slug', $slug)
                    ->when($option->exists, fn ($query) => $query->whereKeyNot($option->id))
                    ->exists()
            ) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }

            $option->slug = $slug;
        });
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attribute_options');
    }
}
