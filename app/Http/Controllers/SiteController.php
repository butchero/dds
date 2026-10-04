<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Support\SiteCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'banners' => SiteCatalog::banners(),
            'products' => Product::query()
                ->with('category')
                ->where('is_published', true)
                ->latest()
                ->limit(12)
                ->get(),
        ]);
    }

    public function search(Request $request): View
    {
        $term = (string) $request->string('s');
        $maker = (string) $request->string('producator');

        return view('products', [
            'title' => $maker !== '' ? $maker : 'Cautare: '.$term,
            'products' => Product::query()
                ->with('category')
                ->where('is_published', true)
                ->when($maker !== '', fn ($query) => $query->where('manufacturer', $maker))
                ->when($term !== '', fn ($query) => $query->where('name', 'like', '%'.$term.'%'))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function category(ProductCategory $productCategory): View
    {
        $ids = $productCategory->children()->pluck('id')->push($productCategory->id);

        return view('products', [
            'title' => $productCategory->name,
            'products' => Product::query()
                ->with('category')
                ->where('is_published', true)
                ->whereIn('product_category_id', $ids)
                ->orderBy('sort')
                ->get(),
        ]);
    }

    public function product(Product $product): View
    {
        abort_unless($product->is_published, 404);

        return view('content', [
            'title' => $product->name,
            'excerpt' => $product->excerpt,
            'blocks' => [[
                'type' => 'text',
                'data' => ['body' => $product->description],
            ]],
        ]);
    }

    public function service(Service $service): View
    {
        abort_unless($service->is_published, 404);

        return view('content', [
            'title' => $service->name,
            'blocks' => $service->blocksForPublic(),
        ]);
    }

    public function news(News $news): View
    {
        abort_unless($news->is_published, 404);

        return view('content', [
            'title' => $news->title,
            'excerpt' => $news->excerpt,
            'blocks' => $news->blocksForPublic(),
        ]);
    }
}
