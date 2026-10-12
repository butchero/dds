<?php

namespace App\Support;

use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class BuderusCatalog
{
    private const string ROOT = 'https://www.buderus.com/ro/ro/ocs/produse/produse-757611-c/';

    private OutputInterface $output;

    private CatalogAttributes $characteristics;

    /** @var array<string, ProductCategory> */
    private array $categoryModels = [];

    /** @var array<string, true> */
    private array $familiesSeen = [];

    /** @var array<string, string> */
    private array $images = [];

    public int $categories = 0;

    public int $products = 0;

    private int $requests = 0;

    public function replace(OutputInterface $output): void
    {
        $this->output = $output;
        $this->characteristics = new CatalogAttributes($output);
        $this->categoryModels = [];
        $this->familiesSeen = [];
        $this->images = [];
        $this->categories = 0;
        $this->products = 0;
        $this->requests = 0;

        $this->wipe();

        $tree = $this->categoryTree($this->document(self::ROOT));
        foreach ($tree as $index => $node) {
            $parent = $this->storeCategory($node, null, $index + 1);

            foreach ($node['children'] as $childIndex => $child) {
                $this->storeCategory($child, $parent, $childIndex + 1);
            }
        }

        $output->writeln("Categorii: {$this->categories}. Produse: {$this->products}. Caracteristici noi: {$this->characteristics->created}. Sărite: {$this->characteristics->skipped}.");
    }

    private function wipe(): void
    {
        $paths = ProductImage::query()->pluck('path')
            ->merge(Product::query()->whereNotNull('image')->pluck('image'))
            ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
            ->unique();

        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }

        Product::query()->delete();
        ProductCategory::query()->update(['parent_id' => null]);
        ProductCategory::query()->delete();
    }

    /**
     * @param  array{id: string, name: string, url: string, children: list<array{id: string, name: string, url: string, children: array{}}>}|array{id: string, name: string, url: string}  $node
     */
    private function storeCategory(array $node, ?ProductCategory $parent, int $sort): ProductCategory
    {
        if (isset($this->categoryModels[$node['id']])) {
            return $this->categoryModels[$node['id']];
        }

        $category = ProductCategory::query()->create([
            'parent_id' => $parent?->id,
            'name' => $node['name'],
            'slug' => $this->uniqueSlug(ProductCategory::class, $node['name'], $node['id']),
            'sort' => $sort,
        ]);
        $this->categoryModels[$node['id']] = $category;
        $this->categories++;

        try {
            $dom = $this->document($node['url']);
        } catch (Throwable) {
            $this->output->writeln('Nu am putut citi '.$node['url']);

            return $category;
        }

        $category->update(['description' => $this->description($dom, 'Intro_content')]);

        foreach ($this->productLinks($dom) as $url) {
            $this->importFamily($url, $category);
        }

        return $category;
    }

    private function importFamily(string $url, ProductCategory $category): void
    {
        if (isset($this->familiesSeen[$url])) {
            return;
        }

        $this->familiesSeen[$url] = true;

        try {
            $dom = $this->document($url);
        } catch (Throwable) {
            $this->output->writeln('Nu am putut citi '.$url);

            return;
        }

        $xpath = new DOMXPath($dom);
        $name = $this->plain($xpath->query('//h1')->item(0));
        $description = $this->productDescription($xpath);
        $excerpt = Str::of($description)->before("\n\n")->trim()->toString();
        $excerpt = $excerpt === '' ? null : $excerpt;
        $gallery = $this->gallery($xpath);
        $variants = $this->variants($xpath);

        if ($variants === []) {
            $api = $xpath->query('//*[@data-variants-api]/@data-variants-api')->item(0)?->nodeValue;
            $variantId = null;

            if (is_string($api) && preg_match('/variantId=(\d+)/', html_entity_decode($api), $matches)) {
                $variantId = $matches[1];
            }

            $variants = [['id' => $variantId, 'name' => $name !== '' ? $name : 'Produs']];
        }

        $manufacturer = Manufacturer::query()->firstOrCreate(['name' => 'Buderus']);

        foreach ($variants as $variant) {
            $product = Product::query()->create([
                'product_category_id' => $category->id,
                'name' => $variant['name'],
                'slug' => $this->uniqueSlug(Product::class, $variant['name'], $variant['id'] ?? $this->buderusId($url) ?? 'produs'),
                'excerpt' => $excerpt,
                'description' => $description === '' ? null : $description,
                'manufacturer_id' => $manufacturer->id,
                'is_published' => true,
                'sort' => $this->products + 1,
            ]);
            ProductCharacteristics::syncGallery($product, $gallery);
            $this->importSpecifications($product, $variant['id'], $xpath);
            $this->products++;
        }
    }

    private function importSpecifications(Product $product, ?string $variantId, DOMXPath $xpath): void
    {
        $api = $xpath->query('//*[@data-variants-api]/@data-variants-api')->item(0)?->nodeValue;

        if (! is_string($api) || $api === '' || $variantId === null) {
            return;
        }

        $url = $this->variantUrl(html_entity_decode($api), $variantId);

        try {
            $payload = json_decode($this->body($url), true);
        } catch (Throwable $exception) {
            $this->output->writeln('Nu am putut citi '.$url.' ('.$exception->getMessage().')');

            return;
        }

        $markup = is_array($payload) ? ($payload['markup'] ?? '') : '';

        if (! is_string($markup) || $markup === '') {
            return;
        }

        $specs = new DOMXPath($this->html($markup));
        $headings = $specs->query('//h3');

        if ($headings === false) {
            return;
        }

        foreach ($headings as $heading) {
            if (! $this->isTechnical($this->plain($heading))) {
                continue;
            }

            $table = $specs->query('following::table[1]', $heading)->item(0);

            if (! $table instanceof DOMNode) {
                continue;
            }

            $rows = $specs->query('.//tr', $table);

            if ($rows === false) {
                continue;
            }

            foreach ($rows as $row) {
                $cells = [];
                $nodes = $specs->query('./th|./td', $row);

                if ($nodes === false) {
                    continue;
                }

                foreach ($nodes as $cell) {
                    $text = $this->plain($cell);

                    if ($text !== '') {
                        $cells[] = $text;
                    }
                }

                if (count($cells) < 2 || $cells[0] === $cells[array_key_last($cells)]) {
                    continue;
                }

                $this->characteristics->assign($product, $cells[0], $cells[array_key_last($cells)]);
            }
        }
    }

    /**
     * @return list<array{id: string, name: string, url: string, children: list<array{id: string, name: string, url: string}>}>
     */
    private function categoryTree(DOMDocument $dom): array
    {
        $xpath = new DOMXPath($dom);
        $lists = $xpath->query('//ul[contains(@class, "Nav_list-level2") and @aria-label="Produse"]');
        $list = null;
        $best = -1;

        if ($lists !== false) {
            foreach ($lists as $candidate) {
                $count = $xpath->query('.//a[contains(@href, "-c/")]', $candidate);

                if ($count !== false && $count->length > $best) {
                    $list = $candidate;
                    $best = $count->length;
                }
            }
        }

        if (! $list instanceof DOMNode) {
            return [];
        }

        $tree = [];
        $items = $xpath->query('./li[contains(@class, "Nav_listItem-level2")]', $list);

        if ($items === false) {
            return [];
        }

        foreach ($items as $item) {
            $link = $xpath->query('./a[contains(@href, "-c/")]', $item)->item(0);

            if (! $link instanceof DOMElement) {
                continue;
            }

            $url = $this->absolute($link->getAttribute('href'));
            $id = $this->buderusId($url);

            if ($id === null || $id === '757611') {
                continue;
            }

            $children = [];
            $nested = $xpath->query('.//ul[contains(@class, "Nav_list-level3")]', $item)->item(0);

            if ($nested instanceof DOMNode) {
                $childLinks = $xpath->query('.//a[contains(@href, "-c/")]', $nested);

                if ($childLinks !== false) {
                    foreach ($childLinks as $childLink) {
                        if (! $childLink instanceof DOMElement) {
                            continue;
                        }

                        $childUrl = $this->absolute($childLink->getAttribute('href'));
                        $childId = $this->buderusId($childUrl);

                        if ($childId === null || $childId === $id || isset($children[$childId])) {
                            continue;
                        }

                        $children[$childId] = [
                            'id' => $childId,
                            'name' => $this->plain($childLink),
                            'url' => $childUrl,
                        ];
                    }
                }
            }

            $tree[] = [
                'id' => $id,
                'name' => $this->plain($link),
                'url' => $url,
                'children' => array_values($children),
            ];
        }

        return $tree;
    }

    /**
     * @return list<string>
     */
    private function productLinks(DOMDocument $dom): array
    {
        $xpath = new DOMXPath($dom);
        $links = $xpath->query('//a[contains(@class, "ProductListItem_content")]/@href');
        $urls = [];

        if ($links === false) {
            return [];
        }

        foreach ($links as $link) {
            $url = $this->absolute($link->nodeValue ?? '');

            if (str_contains($url, '-p/')) {
                $urls[$url] = $url;
            }
        }

        return array_values($urls);
    }

    private function productDescription(DOMXPath $xpath): string
    {
        $blocks = [];
        $intro = $xpath->query('//*[contains(@class, "ProductDescription_text")]')->item(0);

        if ($intro instanceof DOMNode) {
            $paragraphs = (new DOMXPath($intro->ownerDocument))->query('.//p|.//li', $intro);

            if ($paragraphs !== false) {
                foreach ($paragraphs as $paragraph) {
                    $text = $this->plain($paragraph);

                    if ($text !== '') {
                        $blocks[] = $text;
                    }
                }
            }
        }

        $benefits = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " IconListItem ")]');

        if ($benefits !== false) {
            foreach ($benefits as $benefit) {
                $title = $this->plain($xpath->query('.//h3', $benefit)->item(0));
                $copy = $this->plain($xpath->query('.//p', $benefit)->item(0));
                $block = trim($title.($copy !== '' ? "\n".$copy : ''));

                if ($block !== '') {
                    $blocks[] = $block;
                }
            }
        }

        return implode("\n\n", $blocks);
    }

    /**
     * @return list<string>
     */
    private function gallery(DOMXPath $xpath): array
    {
        $gallery = $xpath->query('//*[contains(@class, "GallerySlider_main")]')->item(0);

        if (! $gallery instanceof DOMNode) {
            return [];
        }

        $html = $gallery->ownerDocument?->saveHTML($gallery) ?? '';
        preg_match_all('#https://www\.buderus\.com/ocsmedia/optimized/800x800/[^\s,"\']+#', $html, $matches);
        $paths = [];

        foreach (array_unique($matches[0] ?? []) as $url) {
            $path = $this->download($url);

            if ($path !== null) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function variants(DOMXPath $xpath): array
    {
        $options = $xpath->query('//select[@aria-label="Alegere variantă produs"]/option');
        $variants = [];

        if ($options === false) {
            return [];
        }

        foreach ($options as $option) {
            if (! $option instanceof DOMElement) {
                continue;
            }

            $id = trim($option->getAttribute('value'));
            $name = $this->plain($option);

            if ($id === '' || $name === '') {
                continue;
            }

            $variants[] = ['id' => $id, 'name' => $name];
        }

        return $variants;
    }

    private function description(DOMDocument $dom, string $class): ?string
    {
        $xpath = new DOMXPath($dom);
        $intro = $xpath->query('//*[contains(@class, "'.$class.'")]')->item(0);

        if (! $intro instanceof DOMNode) {
            return null;
        }

        $paragraphs = $xpath->query('.//p', $intro);
        $lines = [];

        if ($paragraphs !== false) {
            foreach ($paragraphs as $paragraph) {
                $text = $this->plain($paragraph);

                if ($text !== '') {
                    $lines[] = $text;
                }
            }
        }

        $text = implode("\n\n", $lines);

        return $text === '' ? null : $text;
    }

    private function download(string $url): ?string
    {
        if (isset($this->images[$url])) {
            return $this->images[$url];
        }

        try {
            $response = $this->request($url);
        } catch (Throwable) {
            $this->output->writeln('Nu am putut descărca '.$url);

            return null;
        }

        $filename = basename(parse_url($url, PHP_URL_PATH) ?: '');

        if ($filename === '' || $filename === '/') {
            return null;
        }

        $path = 'produse/'.$filename;
        Storage::disk('public')->put($path, $response->body());
        $this->images[$url] = $path;

        return $path;
    }

    private function document(string $url): DOMDocument
    {
        return $this->html($this->body($url));
    }

    private function body(string $url): string
    {
        return $this->request($url)->body();
    }

    private function request(string $url): Response
    {
        if ($this->requests > 0) {
            usleep(150000);
        }

        $this->requests++;

        $response = Http::connectTimeout(5)
            ->timeout(30)
            ->retry(5, 2000, function (Throwable $exception): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && ($exception->response === null || $exception->response->serverError() || $exception->response->status() === 429));
            })
            ->withHeaders(['User-Agent' => 'DDS catalog import'])
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException($response->body());
        }

        return $response;
    }

    private function html(string $markup): DOMDocument
    {
        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$markup, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $dom;
    }

    private function variantUrl(string $api, string $variantId): string
    {
        $parts = parse_url($api);
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        $query['variantId'] = $variantId;
        $scheme = isset($parts['scheme']) ? $parts['scheme'].'://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '';

        return $scheme.$host.$port.$path.'?'.http_build_query($query);
    }

    private function absolute(string $href): string
    {
        $href = trim($href);

        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            return $href;
        }

        return 'https://www.buderus.com/'.ltrim($href, '/');
    }

    private function buderusId(string $url): ?string
    {
        if (preg_match('/-(\d+)-[cp]\//', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function isTechnical(string $heading): bool
    {
        return Str::ascii(mb_strtolower($heading)) === 'informatii tehnice';
    }

    private function plain(?DOMNode $node): string
    {
        if ($node === null) {
            return '';
        }

        $text = html_entity_decode($node->textContent ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\u{2014}", "\u{2013}"], '-', $text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }

    /**
     * @param  class-string<Product|ProductCategory>  $model
     */
    private function uniqueSlug(string $model, string $name, string $externalId): string
    {
        $base = Str::slug($name);
        $base = $base === '' ? 'element' : $base;
        $slug = $base;

        if ($model::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$externalId;
        }

        $suffix = 2;

        while ($model::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$externalId.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
