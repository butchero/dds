<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasSlug;

    protected $fillable = [
        'product_category_id', 'name', 'slug', 'excerpt', 'description',
        'image', 'manufacturer', 'price', 'quantity', 'vat_rate', 'is_published', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'price' => 'decimal:2',
            'quantity' => 'integer',
            'vat_rate' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort')->orderBy('id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function selectedOptions(): BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class, 'product_attribute_options')
            ->orderBy('attribute_options.sort')
            ->orderBy('attribute_options.id');
    }

    /**
     * @return Collection<int, array{name: string, value: string}>
     */
    public function specifications(): Collection
    {
        $this->loadMissing(['values.attribute', 'selectedOptions.attribute']);

        $rows = $this->values
            ->map(function (ProductAttributeValue $value): ?array {
                $display = $value->display();

                if ($display === null) {
                    return null;
                }

                return [
                    'name' => $value->attribute->name,
                    'value' => $display,
                    'sort' => $value->attribute->sort,
                ];
            })
            ->filter();

        $optionRows = $this->selectedOptions
            ->groupBy('attribute_id')
            ->map(function (Collection $options): array {
                $attribute = $options->first()->attribute;

                return [
                    'name' => $attribute->name,
                    'value' => $options->pluck('label')->join(', '),
                    'sort' => $attribute->sort,
                ];
            });

        return $rows
            ->concat($optionRows)
            ->sortBy([
                ['sort', 'asc'],
                ['name', 'asc'],
            ])
            ->values()
            ->map(fn (array $row): array => [
                'name' => $row['name'],
                'value' => $row['value'],
            ]);
    }

    /**
     * @return list<array{name: string, value: string}>
     */
    public function listingHighlights(int $limit = 3): array
    {
        $this->loadMissing(['values.attribute', 'selectedOptions.attribute']);

        $rows = $this->values
            ->map(function (ProductAttributeValue $value): ?array {
                if (! $value->attribute->is_filterable) {
                    return null;
                }

                $display = $value->display();

                if ($display === null) {
                    return null;
                }

                return [
                    'name' => $value->attribute->name,
                    'value' => $display,
                    'sort' => $value->attribute->sort,
                ];
            })
            ->filter();

        $optionRows = $this->selectedOptions
            ->groupBy('attribute_id')
            ->map(function (Collection $options): ?array {
                $attribute = $options->first()->attribute;

                if (! $attribute->is_filterable) {
                    return null;
                }

                return [
                    'name' => $attribute->name,
                    'value' => $options->pluck('label')->join(', '),
                    'sort' => $attribute->sort,
                ];
            })
            ->filter();

        return $rows
            ->concat($optionRows)
            ->sortBy([
                ['sort', 'asc'],
                ['name', 'asc'],
            ])
            ->take($limit)
            ->values()
            ->map(fn (array $row): array => [
                'name' => $row['name'],
                'value' => $row['value'],
            ])
            ->all();
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image ? Storage::disk('public')->url($this->image) : null);
    }
}
