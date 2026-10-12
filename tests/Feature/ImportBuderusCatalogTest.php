<?php

namespace Tests\Feature;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportBuderusCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_reuses_characteristics_and_replaces_the_catalog(): void
    {
        Storage::fake('public');
        Http::preventStrayRequests();
        Http::fake(function (Request $request) {
            $url = $request->url();
            $fixture = match (true) {
                str_contains($url, 'produse-757611-c') => 'root.html',
                str_contains($url, 'echipamente-1-c') => 'echipamente.html',
                str_contains($url, 'centrale-2-c') => 'centrale.html',
                str_contains($url, 'centrale-3-c') => 'duplicate.html',
                str_contains($url, 'logamax-10-p') => 'product.html',
                str_contains($url, 'variantId=100') => 'variant-100.json',
                str_contains($url, 'variantId=101') => 'variant-101.json',
                str_contains($url, '/800x800/') => null,
                default => 'missing',
            };

            if ($fixture === null) {
                return Http::response('image', 200);
            }

            if ($fixture === 'missing') {
                return Http::response('missing', 404);
            }

            return Http::response(file_get_contents(base_path('tests/Fixtures/buderus/'.$fixture)));
        });

        Attribute::factory()->ofType(AttributeType::Number)->create([
            'name' => 'Putere termică nominală',
            'slug' => 'putere-termica-nominala',
            'unit' => 'kW',
        ]);
        Attribute::factory()->ofType(AttributeType::Number)->create([
            'name' => 'Observatii',
            'slug' => 'observatii',
        ]);
        Product::query()->create([
            'name' => 'Vechi',
            'slug' => 'vechi',
            'is_published' => true,
        ]);

        $this->artisan('catalog:import-buderus')
            ->expectsOutputToContain('Sărit Observatii')
            ->assertSuccessful();

        $this->assertNull(Product::query()->where('slug', 'vechi')->first());

        $parent = ProductCategory::query()->where('slug', 'echipamente')->first();
        $child = ProductCategory::query()->where('slug', 'centrale')->first();
        $duplicate = ProductCategory::query()->where('slug', 'centrale-3')->first();

        $this->assertNotNull($parent);
        $this->assertSame('Echipamente - Buderus.', $parent->description);
        $this->assertNotNull($child);
        $this->assertSame($parent->id, $child->parent_id);
        $this->assertSame('Centrale cu condensare.', $child->description);
        $this->assertNotNull($duplicate);
        $this->assertSame('Centrale', $duplicate->name);

        $small = Product::query()->where('name', 'GB-24')->first();
        $large = Product::query()->where('name', 'GB-30')->first();

        $this->assertNotNull($small);
        $this->assertNotNull($large);
        $this->assertSame($child->id, $small->product_category_id);
        $this->assertSame('Buderus', $small->manufacturer->name);
        $this->assertSame('Centrală compactă pentru apartament.', $small->excerpt);
        $this->assertStringContainsString('Confort', (string) $small->description);
        $this->assertSame('produse/o1.webp', $small->image);
        $this->assertSame(['produse/o1.webp', 'produse/o2.webp'], $small->images()->orderBy('sort')->pluck('path')->all());
        Storage::disk('public')->assertExists('produse/o1.webp');

        $power = Attribute::query()->where('slug', 'putere-termica-nominala')->first();
        $this->assertNotNull($power);
        $this->assertSame(1, Attribute::query()->where('slug', 'putere-termica-nominala')->count());
        $this->assertSame('24.00', $small->values()->where('attribute_id', $power->id)->first()->value_number);
        $this->assertSame('30.00', $large->values()->where('attribute_id', $power->id)->first()->value_number);

        $ready = Attribute::query()->where('slug', 'h2-ready')->first();
        $this->assertNotNull($ready);
        $this->assertSame(AttributeType::Boolean, $ready->type);
        $this->assertFalse($ready->is_filterable);
        $this->assertTrue($child->attributes()->whereKey($ready->id)->exists());

        $fuel = Attribute::query()->where('slug', 'combustibil-adecvat')->first();
        $this->assertNotNull($fuel);
        $this->assertSame(AttributeType::Text, $fuel->type);
        $this->assertFalse($fuel->is_filterable);
        $this->assertNull(Product::query()->whereHas('values', fn ($query) => $query->where('attribute_id', Attribute::query()->where('slug', 'observatii')->value('id')))->first());
        $this->assertNull(Attribute::query()->where('name', 'Fișa produsului')->first());
    }
}
