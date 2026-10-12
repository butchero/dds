<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use App\Models\News;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Support\Breadcrumbs;
use App\Support\CategoryListing;
use App\Support\PageMeta;
use App\Support\SiteCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class SiteController extends Controller
{
    private const string CATALOG_LAYOUT_COOKIE = 'catalog_layout';

    public function home(): View
    {
        return view('home', [
            'heading' => SiteSetting::get('home_heading'),
            'banners' => SiteCatalog::banners(),
            'brands' => Manufacturer::query()
                ->whereNotNull('logo')
                ->orderBy('name')
                ->get()
                ->map(fn (Manufacturer $manufacturer): array => [
                    'name' => $manufacturer->name,
                    'src' => $manufacturer->logo_url,
                    'slug' => $manufacturer->slug,
                ])
                ->all(),
            'products' => Product::query()
                ->with(['category', 'values.attribute', 'selectedOptions.attribute'])
                ->where('is_published', true)
                ->latest()
                ->limit(12)
                ->get(),
        ]);
    }

    public function search(Request $request): View|RedirectResponse
    {
        $maker = (string) $request->string('producator');

        if ($maker !== '') {
            $manufacturer = Manufacturer::query()->where('name', $maker)->firstOrFail();

            return redirect()->route('manufacturer', $manufacturer, 301);
        }

        $term = (string) $request->string('s');
        $title = __('Cautare: :term', ['term' => $term]);

        return view('products', [
            'title' => $title,
            'description' => __('Rezultate pentru :term.', ['term' => $term]),
            'canonical' => $request->fullUrl(),
            'breadcrumbs' => Breadcrumbs::forPage($title),
            'catalogLayout' => $this->catalogLayout($request),
            'products' => Product::query()
                ->with(['category', 'values.attribute', 'selectedOptions.attribute'])
                ->where('is_published', true)
                ->when($term !== '', fn ($query) => $query->where('name', 'like', '%'.$term.'%'))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function manufacturer(Request $request, Manufacturer $manufacturer): View
    {
        $title = $manufacturer->name;

        return view('products', [
            'title' => $title,
            'description' => __('Produse :maker.', ['maker' => $manufacturer->name]),
            'canonical' => route('manufacturer', $manufacturer),
            'breadcrumbs' => Breadcrumbs::forPage($title),
            'catalogLayout' => $this->catalogLayout($request),
            'products' => $manufacturer->products()
                ->with(['category', 'values.attribute', 'selectedOptions.attribute'])
                ->where('is_published', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function category(Request $request, ProductCategory $productCategory): View
    {
        $listing = CategoryListing::for($productCategory, $request);
        $children = $productCategory->children()->get();
        $parent = $productCategory->parent;
        $hasFilters = $children->isEmpty() && ($listing['manufacturers'] !== []
            || $listing['filters'] !== []
            || $listing['price'] !== null
            || $listing['stock'] !== null);
        $categoryFilters = [
            'category' => $productCategory,
            'filters' => $listing['filters'],
            'manufacturers' => $listing['manufacturers'],
            'price' => $listing['price'],
            'stock' => $listing['stock'],
            'filtered' => $listing['filtered'],
            'total' => $listing['products']->count(),
            'hasFilters' => $hasFilters,
        ];

        $listsEveryCategory = $parent === null && $children->isNotEmpty();

        view()->share('categoryFilters', $categoryFilters);
        view()->share('categoryBox', [
            'title' => __($listsEveryCategory ? 'Categorii' : 'Subcategorii'),
            'children' => $children,
            'parent' => $parent,
            'siblings' => $parent === null ? collect() : $parent->children()->get(),
            'current' => $productCategory,
            'listsEveryCategory' => $listsEveryCategory,
        ]);

        return view('products', [
            'title' => $productCategory->name,
            'description' => $productCategory->description,
            'breadcrumbs' => Breadcrumbs::forCategory($productCategory),
            'products' => $listing['products'],
            'category' => $productCategory,
            'categoryFilters' => $categoryFilters,
            'catalogLayout' => $this->catalogLayout($request),
        ]);
    }

    public function product(Product $product): View
    {
        abort_unless($product->is_published, 404);

        $product->load(['values.attribute', 'selectedOptions.attribute', 'images']);

        $specifications = $product->specifications();
        $images = $product->images->pluck('url')->filter()->values();

        if ($images->isEmpty() && filled($product->image_url)) {
            $images = collect([$product->image_url]);
        }

        return view('product', [
            'product' => $product,
            'breadcrumbs' => Breadcrumbs::forProduct($product),
            'gallery' => [
                'name' => $product->name,
                'images' => $images->all(),
                'description' => $product->description ?: ($product->excerpt ?? ''),
                'specifications' => $specifications->all(),
                'labels' => [
                    'enlarge' => __('Mărește fotografia'),
                    'previous' => __('Fotografia anterioară'),
                    'next' => __('Fotografia următoare'),
                    'description' => __('Descriere'),
                    'specifications' => __('Caracteristici'),
                ],
            ],
        ]);
    }

    public function service(Service $service): View
    {
        abort_unless($service->is_published, 404);

        return view('content', [
            'title' => $service->name,
            'description' => PageMeta::descriptionFromBlocks($service->blocks),
            'image' => PageMeta::imageFromBlocks($service->blocks),
            'breadcrumbs' => Breadcrumbs::forPage($service->name),
            'blocks' => $service->blocksForPublic(),
        ]);
    }

    public function news(News $news): View
    {
        abort_unless($news->is_published, 404);

        return view('content', [
            'title' => $news->title,
            'description' => $news->excerpt ?: PageMeta::descriptionFromBlocks($news->blocks),
            'image' => $news->image,
            'type' => 'article',
            'breadcrumbs' => Breadcrumbs::forPage($news->title),
            'excerpt' => $news->excerpt,
            'blocks' => $news->blocksForPublic(),
        ]);
    }

    private function catalogLayout(Request $request): string
    {
        $choice = match ($request->query('afisare')) {
            'lista' => 'list',
            'grila' => 'grid',
            default => null,
        };

        if ($choice === null) {
            return $request->cookie(self::CATALOG_LAYOUT_COOKIE) === 'list' ? 'list' : 'grid';
        }

        Cookie::queue(self::CATALOG_LAYOUT_COOKIE, $choice, 60 * 24 * 180);

        return $choice;
    }
}
