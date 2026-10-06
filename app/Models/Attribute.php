<?php

namespace App\Models;

use App\Enums\AttributeType;
use App\Models\Concerns\HasSlug;
use Database\Factories\AttributeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    /** @use HasFactory<AttributeFactory> */
    use HasFactory, HasSlug;

    protected $fillable = [
        'name', 'slug', 'type', 'unit', 'is_filterable', 'sort',
    ];

    protected $attributes = [
        'is_filterable' => false,
        'sort' => 0,
    ];

    protected function casts(): array
    {
        return [
            'type' => AttributeType::class,
            'is_filterable' => 'boolean',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class)->orderBy('sort')->orderBy('id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'attribute_product_category');
    }
}
