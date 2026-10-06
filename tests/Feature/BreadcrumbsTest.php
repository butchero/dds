<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class BreadcrumbsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_does_not_render_a_breadcrumb_trail(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('BreadcrumbList', false)
            ->assertDontSee('aria-label="Breadcrumb"', false);
    }

    public function test_category_page_lists_ancestors_in_the_breadcrumb_schema(): void
    {
        $parent = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        $child = ProductCategory::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Pe gaz',
            'slug' => 'pe-gaz',
        ]);

        $response = $this->get(route('category', $child).'?filtru=1');

        $response->assertSee('<a href="'.route('category', $parent).'"', false);
        $response->assertSee('<span aria-current="page" class="text-[#444444]">Pe gaz</span>', false);
        $this->assertSame([
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Acasă',
                'item' => route('home'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Centrale',
                'item' => route('category', $parent),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => 'Pe gaz',
            ],
        ], $this->breadcrumbItems($response));
    }

    public function test_product_page_lists_the_category_trail_before_the_product(): void
    {
        $parent = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        $child = ProductCategory::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Pe gaz',
            'slug' => 'pe-gaz',
        ]);
        $product = Product::query()->create([
            'product_category_id' => $child->id,
            'name' => 'Centrala Ariston',
            'slug' => 'centrala-ariston',
            'is_published' => true,
        ]);

        $response = $this->get(route('product', $product));

        $response->assertSee('<span aria-current="page" class="text-[#444444]">Centrala Ariston</span>', false);
        $this->assertSame([
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Acasă',
                'item' => route('home'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Centrale',
                'item' => route('category', $parent),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => 'Pe gaz',
                'item' => route('category', $child),
            ],
            [
                '@type' => 'ListItem',
                'position' => 4,
                'name' => 'Centrala Ariston',
            ],
        ], $this->breadcrumbItems($response));
    }

    public function test_product_breadcrumb_escapes_a_name_with_markup(): void
    {
        $product = Product::query()->create([
            'name' => '<script>alert(1)</script>',
            'slug' => 'centrala-marcata',
            'is_published' => true,
        ]);

        $response = $this->get(route('product', $product));

        $response
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);

        $current = $this->breadcrumbItems($response)[1];

        $this->assertSame('<script>alert(1)</script>', $current['name']);
        $this->assertArrayNotHasKey('item', $current);
    }

    public function test_search_page_uses_the_search_title_as_the_current_crumb(): void
    {
        $response = $this->get(route('search', ['s' => 'Buderus']));
        $items = $this->breadcrumbItems($response);

        $response->assertSee('<span aria-current="page" class="text-[#444444]">Cautare: Buderus</span>', false);
        $this->assertSame('Cautare: Buderus', $items[1]['name']);
    }

    public function test_service_page_lists_home_and_the_service(): void
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Service',
            'slug' => 'service',
        ]);
        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'name' => 'Revizie centrala',
            'slug' => 'revizie-centrala',
            'is_published' => true,
            'blocks' => [],
        ]);

        $response = $this->get(route('service', $service));

        $this->assertSame([
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Acasă',
                'item' => route('home'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Revizie centrala',
            ],
        ], $this->breadcrumbItems($response));
    }

    #[TestWith(['privacy', 'Politica de confidențialitate'])]
    #[TestWith(['cookies', 'Politica de cookie-uri'])]
    #[TestWith(['login', 'Autentificare'])]
    #[TestWith(['register', 'Cont nou'])]
    public function test_static_page_lists_home_and_its_title(string $route, string $title): void
    {
        $response = $this->get(route($route));

        $items = $this->breadcrumbItems($response);

        $response->assertSee('<span aria-current="page" class="text-[#444444]">'.$title.'</span>', false);
        $this->assertSame($title, $items[1]['name']);
        $this->assertSame(route('home'), $items[0]['item']);
    }

    public function test_account_page_lists_home_and_the_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.home'));

        $items = $this->breadcrumbItems($response);

        $response->assertSee('<span aria-current="page" class="text-[#444444]">Contul meu</span>', false);
        $this->assertSame('Contul meu', $items[1]['name']);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function breadcrumbItems(TestResponse $response): array
    {
        $content = $response->getContent();
        $this->assertIsString($content);

        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $content, $matches);
        $this->assertCount(1, $matches[1]);

        $schema = json_decode($matches[1][0], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('https://schema.org', $schema['@context']);
        $this->assertSame('BreadcrumbList', $schema['@type']);

        return $schema['itemListElement'];
    }
}
