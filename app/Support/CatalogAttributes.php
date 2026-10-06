<?php

namespace App\Support;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\Product;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

class CatalogAttributes
{
    /** @var array<string, Attribute> */
    private array $attributes = [];

    public int $created = 0;

    public int $skipped = 0;

    public function __construct(private readonly ?OutputInterface $output = null) {}

    public function assign(Product $product, string $name, string $raw): void
    {
        $name = preg_replace('/\s+/u', ' ', trim($name)) ?? '';
        $raw = trim($raw);

        if ($name === '' || $raw === '') {
            return;
        }

        $slug = Str::slug($name);

        if ($slug === '') {
            return;
        }

        $inferred = $this->infer($raw);
        $attribute = $this->attributes[$slug] ?? Attribute::query()->where('slug', $slug)->first();

        if (! $attribute instanceof Attribute) {
            $attribute = Attribute::query()->create([
                'name' => $name,
                'slug' => $slug,
                'type' => $inferred['type'],
                'unit' => $inferred['type'] === AttributeType::Number ? $inferred['unit'] : null,
                'is_filterable' => $inferred['type']->filtersByDefault(),
                'sort' => 0,
            ]);
            $this->created++;
        } elseif ($attribute->type !== AttributeType::Text && $attribute->type !== $inferred['type']) {
            $this->skipped++;
            $this->output?->writeln("Sărit {$name}: valoarea nu se potrivește tipului.");

            return;
        }

        $this->attributes[$slug] = $attribute;

        if ($product->product_category_id !== null) {
            $attribute->categories()->syncWithoutDetaching([$product->product_category_id]);
        }

        if ($attribute->type === AttributeType::Text) {
            $product->values()->updateOrCreate(
                ['attribute_id' => $attribute->id],
                ['value_text' => $raw, 'value_number' => null, 'value_boolean' => null],
            );

            return;
        }

        if ($attribute->type === AttributeType::Boolean) {
            $product->values()->updateOrCreate(
                ['attribute_id' => $attribute->id],
                ['value_text' => null, 'value_number' => null, 'value_boolean' => $inferred['boolean']],
            );

            return;
        }

        if ($attribute->unit === null && $inferred['unit'] !== null) {
            $attribute->update(['unit' => $inferred['unit']]);
        }

        $product->values()->updateOrCreate(
            ['attribute_id' => $attribute->id],
            ['value_text' => null, 'value_number' => $inferred['number'], 'value_boolean' => null],
        );
    }

    /**
     * @return array{type: AttributeType, unit: ?string, number: ?string, boolean: ?bool}
     */
    private function infer(string $raw): array
    {
        $empty = [
            'type' => AttributeType::Text,
            'unit' => null,
            'number' => null,
            'boolean' => null,
        ];
        $lower = mb_strtolower($raw);

        if ($lower === 'da' || $lower === 'nu') {
            return [
                'type' => AttributeType::Boolean,
                'unit' => null,
                'number' => null,
                'boolean' => $lower === 'da',
            ];
        }

        if (! preg_match('/^(-?\d+(?:[.,]\d+)?)(.*)$/u', $raw, $matches)) {
            return $empty;
        }

        $unit = trim($matches[2]);

        if ($unit !== '' && ! preg_match('/^[%°\p{L}]/u', $unit)) {
            return $empty;
        }

        return [
            'type' => AttributeType::Number,
            'unit' => $unit === '' ? null : $unit,
            'number' => str_replace(',', '.', $matches[1]),
            'boolean' => null,
        ];
    }
}
