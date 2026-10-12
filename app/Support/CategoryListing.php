<?php

namespace App\Support;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CategoryListing
{
    /**
     * Values of one characteristic are combined with OR. Different characteristics are combined with AND.
     *
     * @return array{
     *     products: Collection<int, Product>,
     *     filters: list<array<string, mixed>>,
     *     manufacturers: list<array{name: string, count: int, checked: bool}>,
     *     price: ?array{min: string, max: string},
     *     stock: ?array{checked: bool, count: int},
     *     filtered: bool
     * }
     */
    public static function for(ProductCategory $category, Request $request): array
    {
        $childIds = $category->children()->pluck('id')->map(fn ($id): int => (int) $id);
        $listingCategoryIds = [...$childIds->all(), (int) $category->id];

        $attributes = $childIds->isEmpty()
            ? Attribute::query()
                ->where('is_filterable', true)
                ->whereHas('categories', fn (Builder $query) => $query->where('product_categories.id', $category->id))
                ->with('options')
                ->orderBy('sort')
                ->orderBy('id')
                ->get()
            : collect();

        $raw = $request->query('c', []);
        $raw = is_array($raw) ? $raw : [];
        $selectedMakers = self::stringList($request->query('producator'));
        $priceInput = $request->query('pret');
        $priceMin = is_array($priceInput) ? self::numeric($priceInput['min'] ?? null) : null;
        $priceMax = is_array($priceInput) ? self::numeric($priceInput['max'] ?? null) : null;
        $stockOnly = $request->query('stoc') === '1';

        $pool = Product::query()
            ->with(['category', 'manufacturer', 'values.attribute', 'selectedOptions.attribute'])
            ->where('is_published', true)
            ->whereIn('product_category_id', $listingCategoryIds)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        $showPrice = $childIds->isEmpty() && $pool->contains(fn (Product $product): bool => $product->price !== null);
        $showStock = $childIds->isEmpty() && $pool->contains(fn (Product $product): bool => $product->quantity !== null);

        $constraints = self::constraints($attributes, $raw, $selectedMakers, $showPrice ? $priceMin : null, $showPrice ? $priceMax : null, $showStock && $stockOnly);

        $products = $pool
            ->filter(fn (Product $product): bool => self::matches($product, $constraints))
            ->values();

        return [
            'products' => $products,
            'filters' => self::filters($attributes, $pool, $constraints, $raw),
            'manufacturers' => $childIds->isEmpty() ? self::manufacturers($pool, $constraints, $selectedMakers) : [],
            'price' => $showPrice ? [
                'min' => $priceMin ?? '',
                'max' => $priceMax ?? '',
            ] : null,
            'stock' => $showStock ? [
                'checked' => $stockOnly,
                'count' => $pool->filter(fn (Product $product): bool => self::matches($product, $constraints, 'stock') && $product->quantity !== null && $product->quantity > 0)->count(),
            ] : null,
            'filtered' => $constraints !== [],
        ];
    }

    /**
     * @param  Collection<int, Attribute>  $attributes
     * @param  array<string, mixed>  $raw
     * @param  list<string>  $selectedMakers
     * @return array<string, callable(Product): bool>
     */
    private static function constraints(Collection $attributes, array $raw, array $selectedMakers, ?string $priceMin, ?string $priceMax, bool $stockOnly): array
    {
        $constraints = [];

        foreach ($attributes as $attribute) {
            $check = self::attributeConstraint($attribute, $raw[$attribute->slug] ?? null);

            if ($check !== null) {
                $constraints[$attribute->slug] = $check;
            }
        }

        if ($selectedMakers !== []) {
            $constraints['manufacturer'] = fn (Product $product): bool => in_array((string) $product->manufacturer?->name, $selectedMakers, true);
        }

        if ($priceMin !== null || $priceMax !== null) {
            $constraints['price'] = function (Product $product) use ($priceMin, $priceMax): bool {
                if ($product->price === null) {
                    return false;
                }

                $price = (float) $product->price;

                if ($priceMin !== null && $price < (float) $priceMin) {
                    return false;
                }

                return $priceMax === null || $price <= (float) $priceMax;
            };
        }

        if ($stockOnly) {
            $constraints['stock'] = fn (Product $product): bool => $product->quantity !== null && $product->quantity > 0;
        }

        return $constraints;
    }

    /**
     * @param  array<string, callable(Product): bool>  $constraints
     */
    private static function matches(Product $product, array $constraints, ?string $skip = null): bool
    {
        foreach ($constraints as $key => $check) {
            if ($key === $skip) {
                continue;
            }

            if (! $check($product)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return ?callable(Product): bool
     */
    private static function attributeConstraint(Attribute $attribute, mixed $input): ?callable
    {
        if ($attribute->type === AttributeType::Text) {
            $terms = self::stringList($input);

            if ($terms === []) {
                return null;
            }

            return fn (Product $product): bool => in_array(self::productText($product, $attribute), $terms, true);
        }

        if ($attribute->type === AttributeType::Number) {
            $keys = array_values(array_filter(array_map(
                self::numberKey(...),
                self::stringList($input),
            )));

            if ($keys === []) {
                return null;
            }

            return fn (Product $product): bool => in_array(self::productNumberKey($product, $attribute), $keys, true);
        }

        if ($attribute->type === AttributeType::Boolean) {
            $choices = self::stringList($input);
            $bools = [];

            if (in_array('da', $choices, true)) {
                $bools[] = true;
            }

            if (in_array('nu', $choices, true)) {
                $bools[] = false;
            }

            if ($bools === []) {
                return null;
            }

            return fn (Product $product): bool => $product->values->contains(fn ($value): bool => (int) $value->attribute_id === (int) $attribute->id
                && $value->value_boolean !== null
                && in_array((bool) $value->value_boolean, $bools, true));
        }

        $slugs = self::stringList($input);
        $optionIds = $attribute->options->whereIn('slug', $slugs)->pluck('id');

        if ($optionIds->isEmpty()) {
            return null;
        }

        return fn (Product $product): bool => $product->selectedOptions->whereIn('id', $optionIds)->isNotEmpty();
    }

    /**
     * @param  Collection<int, Attribute>  $attributes
     * @param  Collection<int, Product>  $pool
     * @param  array<string, callable(Product): bool>  $constraints
     * @param  array<string, mixed>  $raw
     * @return list<array<string, mixed>>
     */
    private static function filters(Collection $attributes, Collection $pool, array $constraints, array $raw): array
    {
        $filters = [];

        foreach ($attributes as $attribute) {
            $input = $raw[$attribute->slug] ?? null;
            $eligible = $pool->filter(fn (Product $product): bool => self::matches($product, $constraints, $attribute->slug));
            $options = self::options($attribute, $pool, $eligible, $input);

            if ($options === []) {
                continue;
            }

            $filters[] = [
                'name' => $attribute->name,
                'slug' => $attribute->slug,
                'options' => $options,
            ];
        }

        return $filters;
    }

    /**
     * @param  Collection<int, Product>  $pool
     * @param  Collection<int, Product>  $eligible
     * @return list<array{slug: string, label: string, checked: bool, count: int}>
     */
    private static function options(Attribute $attribute, Collection $pool, Collection $eligible, mixed $input): array
    {
        if ($attribute->type === AttributeType::Text) {
            $selected = self::stringList($input);

            return $pool
                ->map(fn (Product $product): ?string => self::productText($product, $attribute))
                ->filter(fn (?string $text): bool => $text !== null)
                ->unique()
                ->sort()
                ->values()
                ->map(function (string $text) use ($attribute, $eligible, $selected): array {
                    return [
                        'slug' => $text,
                        'label' => $text,
                        'checked' => in_array($text, $selected, true),
                        'count' => $eligible->filter(fn (Product $product): bool => self::productText($product, $attribute) === $text)->count(),
                    ];
                })
                ->filter(fn (array $option): bool => $option['count'] > 0)
                ->values()
                ->all();
        }

        if ($attribute->type === AttributeType::Number) {
            $selected = array_values(array_filter(array_map(self::numberKey(...), self::stringList($input))));

            return $pool
                ->map(function (Product $product) use ($attribute): ?ProductAttributeValue {
                    $value = $product->values->first(fn ($row): bool => (int) $row->attribute_id === (int) $attribute->id);

                    if (! $value instanceof ProductAttributeValue || self::productNumberKey($product, $attribute) === null) {
                        return null;
                    }

                    return $value;
                })
                ->filter()
                ->unique(fn (ProductAttributeValue $value): string => (string) self::numberKey($value->value_number))
                ->sortBy(fn (ProductAttributeValue $value): float => (float) $value->value_number)
                ->values()
                ->map(function (ProductAttributeValue $value) use ($attribute, $eligible, $selected): array {
                    $key = (string) self::numberKey($value->value_number);

                    return [
                        'slug' => $key,
                        'label' => (string) $value->display(),
                        'checked' => in_array($key, $selected, true),
                        'count' => $eligible->filter(fn (Product $product): bool => self::productNumberKey($product, $attribute) === $key)->count(),
                    ];
                })
                ->filter(fn (array $option): bool => $option['count'] > 0)
                ->values()
                ->all();
        }

        if ($attribute->type === AttributeType::Boolean) {
            $selected = self::stringList($input);
            $options = [];

            foreach (['da' => true, 'nu' => false] as $slug => $boolean) {
                $count = $eligible->filter(fn (Product $product): bool => $product->values->contains(
                    fn ($value): bool => (int) $value->attribute_id === (int) $attribute->id && $value->value_boolean === $boolean,
                ))->count();

                if ($count === 0) {
                    continue;
                }

                $options[] = [
                    'slug' => $slug,
                    'label' => $boolean ? __('Da') : __('Nu'),
                    'checked' => in_array($slug, $selected, true),
                    'count' => $count,
                ];
            }

            return $options;
        }

        $selected = self::stringList($input);

        return $attribute->options
            ->map(fn ($option): array => [
                'slug' => $option->slug,
                'label' => $option->label,
                'checked' => in_array($option->slug, $selected, true),
                'count' => $eligible->filter(fn (Product $product): bool => $product->selectedOptions->contains('id', $option->id))->count(),
            ])
            ->filter(fn (array $option): bool => $option['count'] > 0)
            ->values()
            ->all();
    }

    private static function productText(Product $product, Attribute $attribute): ?string
    {
        $value = $product->values->first(fn ($row): bool => (int) $row->attribute_id === (int) $attribute->id);

        if ($value === null || ! is_string($value->value_text) || $value->value_text === '') {
            return null;
        }

        return $value->value_text;
    }

    private static function productNumberKey(Product $product, Attribute $attribute): ?string
    {
        $value = $product->values->first(fn ($row): bool => (int) $row->attribute_id === (int) $attribute->id);

        if ($value === null) {
            return null;
        }

        return self::numberKey($value->value_number);
    }

    private static function numberKey(mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            return null;
        }

        $value = str_replace(',', '.', trim((string) $value));

        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    /**
     * @param  Collection<int, Product>  $pool
     * @param  array<string, callable(Product): bool>  $constraints
     * @param  list<string>  $selectedMakers
     * @return list<array{name: string, count: int, checked: bool}>
     */
    private static function manufacturers(Collection $pool, array $constraints, array $selectedMakers): array
    {
        $eligible = $pool->filter(fn (Product $product): bool => self::matches($product, $constraints, 'manufacturer'));

        return $pool
            ->map(fn (Product $product): ?string => $product->manufacturer?->name)
            ->filter(fn (?string $name): bool => is_string($name) && $name !== '')
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $name): array => [
                'name' => $name,
                'count' => $eligible->filter(fn (Product $product): bool => $product->manufacturer?->name === $name)->count(),
                'checked' => in_array($name, $selectedMakers, true),
            ])
            ->filter(fn (array $maker): bool => $maker['count'] > 0)
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private static function stringList(mixed $input): array
    {
        if (is_string($input) && $input !== '') {
            return [$input];
        }

        if (! is_array($input)) {
            return [];
        }

        return array_values(array_filter(
            $input,
            fn (mixed $value): bool => is_string($value) && $value !== '',
        ));
    }

    private static function numeric(mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return $value;
    }
}
