<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Support\SiteCatalog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Home', [
            'banners' => SiteCatalog::banners(),
            'products' => Product::query()
                ->with('category')
                ->where('is_published', true)
                ->latest()
                ->limit(12)
                ->get()
                ->map(fn (Product $product) => $this->card($product)),
        ]);
    }

    public function search(Request $request): Response
    {
        $term = (string) $request->string('s');
        $maker = (string) $request->string('producator');

        return Inertia::render('Products', [
            'title' => $maker !== '' ? $maker : 'Cautare: '.$term,
            'products' => Product::query()
                ->with('category')
                ->where('is_published', true)
                ->when($maker !== '', fn ($query) => $query->where('manufacturer', $maker))
                ->when($term !== '', fn ($query) => $query->where('name', 'like', '%'.$term.'%'))
                ->orderBy('name')
                ->get()
                ->map(fn (Product $product) => $this->card($product)),
        ]);
    }

    public function category(ProductCategory $productCategory): Response
    {
        $ids = $productCategory->children()->pluck('id')->push($productCategory->id);

        return Inertia::render('Products', [
            'title' => $productCategory->name,
            'products' => Product::query()
                ->with('category')
                ->where('is_published', true)
                ->whereIn('product_category_id', $ids)
                ->orderBy('sort')
                ->get()
                ->map(fn (Product $product) => $this->card($product)),
        ]);
    }

    public function product(Product $product): Response
    {
        abort_unless($product->is_published, 404);

        return Inertia::render('Content', [
            'title' => $product->name,
            'excerpt' => $product->excerpt,
            'blocks' => [[
                'type' => 'text',
                'data' => ['body' => $product->description],
            ]],
        ]);
    }

    public function service(Service $service): Response
    {
        abort_unless($service->is_published, 404);

        return Inertia::render('Content', [
            'title' => $service->name,
            'blocks' => $service->blocksForPublic(),
        ]);
    }

    public function news(News $news): Response
    {
        abort_unless($news->is_published, 404);

        return Inertia::render('Content', [
            'title' => $news->title,
            'excerpt' => $news->excerpt,
            'blocks' => $news->blocksForPublic(),
        ]);
    }

    private function card(Product $product): array
    {
        return [
            'name' => $product->name,
            'slug' => $product->slug,
            'image_url' => $product->image_url,
            'manufacturer' => $product->manufacturer,
            'category_slug' => $product->category?->slug,
        ];
    }
}
