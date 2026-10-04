<?php

namespace App\Support;

use App\Models\Banner;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceCategory;
use App\Models\SiteSetting;

class SiteCatalog
{
    public static function shared(): array
    {
        $settings = SiteSetting::current();

        return [
            'menuPosition' => $settings->menu_position,
            'menu' => MenuItem::query()->orderBy('sort')->get(['label', 'url']),
            'productCategories' => ProductCategory::query()
                ->whereNull('parent_id')
                ->with('children:id,parent_id,name,slug')
                ->orderBy('sort')
                ->get(['id', 'name', 'slug']),
            'serviceCategories' => ServiceCategory::query()
                ->where('show_in_sidebar', true)
                ->with(['services' => fn ($query) => $query->where('is_published', true)->orderBy('sort')->select('id', 'service_category_id', 'name', 'slug')])
                ->orderBy('sort')
                ->get(['id', 'name', 'slug']),
            'hasProducts' => Product::query()->where('is_published', true)->exists(),
            'manufacturers' => Product::query()
                ->where('is_published', true)
                ->whereNotNull('manufacturer')
                ->selectRaw('manufacturer, COUNT(*) as total')
                ->groupBy('manufacturer')
                ->orderBy('manufacturer')
                ->get()
                ->map(fn (Product $product) => [
                    'name' => $product->manufacturer,
                    'total' => (int) $product->total,
                ]),
            'news' => News::query()
                ->where('is_published', true)
                ->latest('published_at')
                ->limit(4)
                ->get(['title', 'slug', 'excerpt', 'published_at']),
            'promos' => Product::query()
                ->where('is_published', true)
                ->whereNotNull('image')
                ->orderBy('sort')
                ->limit(2)
                ->get()
                ->map(fn (Product $product) => [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image_url' => $product->image_url,
                ]),
        ];
    }

    public static function banners(): array
    {
        return Banner::query()->published()->get()->map(fn (Banner $banner) => [
            'title' => $banner->title,
            'url' => $banner->image_url,
            'link' => $banner->link,
        ])->all();
    }
}
