<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Js;

class Breadcrumbs
{
    /**
     * @param  list<array{label: string, url: string|null}>  $items
     * @return list<array{label: string, url: string|null}>
     */
    public static function forPage(string $label): array
    {
        return self::trail([
            ['label' => $label, 'url' => null],
        ]);
    }

    /**
     * @return list<array{label: string, url: string|null}>
     */
    public static function forAccount(string $label): array
    {
        return self::trail([
            ['label' => __('Contul meu'), 'url' => route('account.home')],
            ['label' => $label, 'url' => null],
        ]);
    }

    /**
     * @return list<array{label: string, url: string|null}>
     */
    public static function forCategory(ProductCategory $category): array
    {
        return self::trail($category->lineage()->map(fn (ProductCategory $ancestor): array => [
            'label' => $ancestor->name,
            'url' => $ancestor->is($category) ? null : route('category', $ancestor),
        ])->all());
    }

    /**
     * @return list<array{label: string, url: string|null}>
     */
    public static function forProduct(Product $product): array
    {
        $product->loadMissing('category');

        $items = $product->category
            ? $product->category->lineage()->map(fn (ProductCategory $ancestor): array => [
                'label' => $ancestor->name,
                'url' => route('category', $ancestor),
            ])->all()
            : [];

        $items[] = ['label' => $product->name, 'url' => null];

        return self::trail($items);
    }

    /**
     * BreadcrumbList JSON-LD. The last crumb omits its URL so Google uses the page address.
     *
     * @param  list<array{label: string, url: string|null}>  $items
     */
    public static function schema(array $items): string
    {
        $elements = [];

        foreach (array_values($items) as $index => $item) {
            $element = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
            ];

            if (filled($item['url'])) {
                $element['item'] = $item['url'];
            }

            $elements[] = $element;
        }

        return Js::encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ], JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param  list<array{label: string, url: string|null}>  $items
     * @return list<array{label: string, url: string|null}>
     */
    private static function trail(array $items): array
    {
        return [
            ['label' => __('Acasă'), 'url' => route('home')],
            ...array_values($items),
        ];
    }
}
