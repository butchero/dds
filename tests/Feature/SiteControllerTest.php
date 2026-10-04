<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_published_product_name_in_html(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);

        Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala Ariston Clas',
            'slug' => 'centrala-ariston-clas',
            'is_published' => true,
        ]);

        $this->get(route('home'))
            ->assertSee('Centrala Ariston Clas')
            ->assertSee('<h1', false)
            ->assertDontSee('data-page', false);
    }

    public function test_unpublished_product_returns_not_found(): void
    {
        $product = Product::query()->create([
            'name' => 'Centrala ascunsa',
            'slug' => 'centrala-ascunsa',
            'is_published' => false,
        ]);

        $this->get(route('product', $product))->assertNotFound();
    }

    public function test_product_description_is_escaped(): void
    {
        $product = Product::query()->create([
            'name' => 'Centrala publica',
            'slug' => 'centrala-publica',
            'description' => '<script>alert(1)</script>',
            'is_published' => true,
        ]);

        $this->get(route('product', $product))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_search_renders_only_the_matching_product(): void
    {
        Product::query()->create([
            'name' => 'Centrala Buderus',
            'slug' => 'centrala-buderus',
            'is_published' => true,
        ]);

        Product::query()->create([
            'name' => 'Pompa de caldura',
            'slug' => 'pompa-de-caldura',
            'is_published' => true,
        ]);

        $this->get(route('search', ['s' => 'Buderus']))
            ->assertSee('Centrala Buderus')
            ->assertDontSee('Pompa de caldura');
    }

    public function test_service_page_renders_block_text(): void
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Service',
            'slug' => 'service',
            'show_in_sidebar' => true,
        ]);

        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'name' => 'Revizie centrala',
            'slug' => 'revizie-centrala',
            'is_published' => true,
            'blocks' => [
                ['type' => 'text', 'data' => ['body' => 'Verificare anuala a centralei']],
            ],
        ]);

        $this->get(route('service', $service))
            ->assertSee('Revizie centrala')
            ->assertSee('Verificare anuala a centralei');
    }
}
