<?php

namespace App\Support;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Storage;

class ProductCharacteristics
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function create(array $data): Product
    {
        $rows = $data['characteristic_values'] ?? [];
        $gallery = $data['gallery'] ?? [];
        unset($data['characteristic_values'], $data['gallery']);

        $product = Product::query()->create($data);
        self::sync($product, is_array($rows) ? $rows : []);
        self::syncGallery($product, is_array($gallery) ? $gallery : []);

        return $product;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function update(Product $product, array $data): void
    {
        $rows = $data['characteristic_values'] ?? [];
        $gallery = $data['gallery'] ?? [];
        unset($data['characteristic_values'], $data['gallery']);

        $product->update($data);
        self::sync($product, is_array($rows) ? $rows : []);
        self::syncGallery($product, is_array($gallery) ? $gallery : []);
    }

    /**
     * @param  array<int|string, mixed>  $paths
     */
    public static function syncGallery(Product $product, array $paths): void
    {
        $paths = array_values(array_filter(
            $paths,
            fn (mixed $path): bool => is_string($path) && $path !== '',
        ));

        $removed = $product->images()->pluck('path')->diff($paths);

        foreach ($removed as $path) {
            Storage::disk('public')->delete($path);
        }

        if ($paths === []) {
            $product->images()->delete();
        } else {
            $product->images()->whereNotIn('path', $paths)->delete();
        }

        foreach ($paths as $sort => $path) {
            $product->images()->updateOrCreate(
                ['path' => $path],
                ['sort' => $sort],
            );
        }

        $product->update(['image' => $paths[0] ?? null]);
    }

    /**
     * @return list<string>
     */
    public static function galleryForProduct(Product $product): array
    {
        $product->loadMissing('images');
        $paths = $product->images->pluck('path')->filter()->values()->all();

        if ($paths === [] && filled($product->image)) {
            return [$product->image];
        }

        return $paths;
    }

    /**
     * @param  array<int|string, mixed>  $rows
     */
    public static function sync(Product $product, array $rows): void
    {
        $product->unsetRelation('category');
        $product->load('category');

        $allowed = $product->category?->characteristicsForProduct()->keyBy('id') ?? collect();
        $keptValues = [];
        $keptOptions = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $attribute = $allowed->get((int) ($row['attribute_id'] ?? 0));

            if (! $attribute instanceof Attribute) {
                continue;
            }

            if ($attribute->type->hasOptions()) {
                $requested = $attribute->type === AttributeType::Select
                    ? [(int) ($row['option_id'] ?? 0)]
                    : array_map(intval(...), (array) ($row['option_ids'] ?? []));

                foreach ($attribute->options->whereIn('id', array_filter($requested)) as $option) {
                    $keptOptions[] = $option->id;
                }

                continue;
            }

            $payload = self::scalarPayload($attribute->type, $row);
            $empty = $payload['value_text'] === null
                && $payload['value_number'] === null
                && $payload['value_boolean'] === null;

            if ($empty) {
                continue;
            }

            $product->values()->updateOrCreate(
                ['attribute_id' => $attribute->id],
                $payload,
            );
            $keptValues[] = $attribute->id;
        }

        if ($keptValues === []) {
            $product->values()->delete();
        } else {
            $product->values()->whereNotIn('attribute_id', $keptValues)->delete();
        }

        $product->selectedOptions()->sync($keptOptions);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function rowsForProduct(Product $product): array
    {
        $product->loadMissing(['values', 'selectedOptions']);
        $current = [];

        foreach ($product->values as $value) {
            $current[] = [
                'attribute_id' => $value->attribute_id,
                'value_text' => $value->value_text,
                'value_number' => $value->value_number,
                'value_boolean' => $value->value_boolean === null ? null : ($value->value_boolean ? '1' : '0'),
            ];
        }

        foreach ($product->selectedOptions->groupBy('attribute_id') as $attributeId => $options) {
            $current[] = [
                'attribute_id' => $attributeId,
                'option_id' => $options->count() === 1 ? $options->first()->id : null,
                'option_ids' => $options->pluck('id')->all(),
            ];
        }

        return self::rowsForCategory($product->product_category_id, $current);
    }

    /**
     * @param  array<int|string, mixed>  $current
     * @return list<array<string, mixed>>
     */
    public static function rowsForCategory(?int $categoryId, array $current = []): array
    {
        if ($categoryId === null) {
            return [];
        }

        $category = ProductCategory::query()->find($categoryId);

        if ($category === null) {
            return [];
        }

        $currentByAttribute = [];

        foreach ($current as $row) {
            if (! is_array($row) || ! isset($row['attribute_id'])) {
                continue;
            }

            $currentByAttribute[(int) $row['attribute_id']] = $row;
        }

        return $category->characteristicsForProduct()
            ->map(function (Attribute $attribute) use ($currentByAttribute): array {
                $existing = $currentByAttribute[$attribute->id] ?? [];

                return [
                    'attribute_id' => $attribute->id,
                    'type' => $attribute->type->value,
                    'name' => $attribute->name,
                    'unit' => $attribute->unit,
                    'value_text' => $existing['value_text'] ?? null,
                    'value_number' => $existing['value_number'] ?? null,
                    'value_boolean' => $existing['value_boolean'] ?? null,
                    'option_id' => $existing['option_id'] ?? null,
                    'option_ids' => array_values((array) ($existing['option_ids'] ?? [])),
                ];
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{value_text: ?string, value_number: ?string, value_boolean: ?bool}
     */
    private static function scalarPayload(AttributeType $type, array $row): array
    {
        return match ($type) {
            AttributeType::Text => [
                'value_text' => self::text($row['value_text'] ?? null),
                'value_number' => null,
                'value_boolean' => null,
            ],
            AttributeType::Number => [
                'value_text' => null,
                'value_number' => self::number($row['value_number'] ?? null),
                'value_boolean' => null,
            ],
            AttributeType::Boolean => [
                'value_text' => null,
                'value_number' => null,
                'value_boolean' => self::boolean($row['value_boolean'] ?? null),
            ],
            AttributeType::Select, AttributeType::Multiselect => [
                'value_text' => null,
                'value_number' => null,
                'value_boolean' => null,
            ],
        };
    }

    private static function text(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function number(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (string) $value;
    }

    private static function boolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value === true || $value === 1 || $value === '1') {
            return true;
        }

        if ($value === false || $value === 0 || $value === '0') {
            return false;
        }

        return null;
    }
}
