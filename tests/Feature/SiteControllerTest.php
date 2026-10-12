<?php

namespace Tests\Feature;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Banner;
use App\Models\Manufacturer;
use App\Models\News;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SiteSetting;
use App\Models\User;
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

    public function test_footer_copyright_uses_the_start_year_setting_and_the_current_year(): void
    {
        $this->travelTo('2031-06-15');
        SiteSetting::set('copyright_start_year', '1995');

        $this->get(route('home'))
            ->assertSee('© 1995-2031', false);
    }

    public function test_footer_copyright_defaults_to_2008_through_the_current_year(): void
    {
        $this->travelTo('2026-10-09');

        $this->get(route('home'))
            ->assertSee('© 2008-2026', false);
    }

    public function test_footer_omits_the_all_searches_link(): void
    {
        $this->get(route('home'))
            ->assertDontSee('Toate cautarile');
    }

    public function test_home_renders_the_default_heading_as_its_only_h1(): void
    {
        $heading = 'Centrale termice Constanta - aparate aer conditionat Constanta';

        $response = $this->get(route('home'));

        $response->assertSee('<h1 class="mb-2 text-lg font-medium tracking-tight text-brand">'.$heading.'</h1>', false);
        $response->assertSee('<title>'.$heading.'</title>', false);
        $response->assertDontSee('text-[#b7b7b7]', false);
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    public function test_home_renders_a_saved_heading_and_other_pages_omit_it(): void
    {
        SiteSetting::set('home_heading', '<script>alert(1)</script>');

        $home = $this->get(route('home'));

        $home->assertSee('<h1 class="mb-2 text-lg font-medium tracking-tight text-brand">&lt;script&gt;alert(1)&lt;/script&gt;</h1>', false);
        $home->assertDontSee('<script>alert(1)</script>', false);
        $this->assertSame(1, substr_count($home->getContent(), '<h1'));
        $this->get(route('login'))->assertDontSee('alert(1)');
    }

    public function test_home_uses_the_saved_heading_as_the_hero_h1(): void
    {
        Banner::query()->create([
            'title' => 'Centrale termice',
            'image' => 'banners/hero.jpg',
            'is_published' => true,
            'sort' => 0,
        ]);
        SiteSetting::set('home_heading', 'Titlu din hero');

        $response = $this->get(route('home'));

        $response->assertSee('<h1 class="text-3xl font-medium tracking-tight text-balance sm:text-4xl sm:leading-tight">Titlu din hero</h1>', false);
        $response->assertDontSee('<h1 class="mb-2 text-lg font-medium tracking-tight text-brand">', false);
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    public function test_home_publishes_description_and_share_tags(): void
    {
        $heading = 'Centrale termice Constanta - aparate aer conditionat Constanta';
        $description = 'DDS Services Group din Constanța comercializează și montează centrale termice și aparate de aer condiționat, cu service și verificări ISCIR.';

        $response = $this->get(route('home'));

        $response->assertSee('<title>'.$heading.'</title>', false);
        $response->assertSee('<link rel="canonical" href="'.route('home').'">', false);
        $response->assertSee('<meta name="description" content="'.$description.'">', false);
        $response->assertSee('<meta property="og:title" content="'.$heading.'">', false);
        $response->assertSee('<meta property="og:description" content="'.$description.'">', false);
        $response->assertSee('<meta property="og:url" content="'.route('home').'">', false);
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('<meta name="twitter:card" content="summary">', false);
        $response->assertSee('<meta name="twitter:title" content="'.$heading.'">', false);
        $response->assertDontSee('property="og:image"', false);
    }

    public function test_product_share_tags_use_the_excerpt_and_cover(): void
    {
        $product = Product::query()->create([
            'name' => 'Centrala cu share',
            'slug' => 'centrala-cu-share',
            'excerpt' => '5 > 3 & "ok" <script>alert(1)</script>',
            'image' => 'produse/coperta.jpg',
            'is_published' => true,
        ]);

        $response = $this->get(route('product', $product));

        $response->assertSee('<title>Centrala cu share - DDS Services Group</title>', false);
        $response->assertSee('<meta name="description" content="5 &gt; 3 &amp; &quot;ok&quot; alert(1)">', false);
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertDontSee('5 > 3 & "ok"', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $this->assertMatchesRegularExpression(
            '#<meta property="og:image" content="https?://[^"]+/storage/produse/coperta.jpg">#',
            $response->getContent(),
        );
        $this->assertMatchesRegularExpression(
            '#"images":\["https?://[^"]+/storage/produse/coperta.jpg"\]#',
            $response->getContent(),
        );
    }

    public function test_product_without_an_excerpt_uses_a_short_description_and_the_share_image(): void
    {
        SiteSetting::set('share_image', 'site/share.png');
        $product = Product::query()->create([
            'name' => 'Centrala fara rezumat',
            'slug' => 'centrala-fara-rezumat',
            'description' => str_repeat('alfa ', 40).'omegaunic',
            'is_published' => true,
        ]);

        $response = $this->get(route('product', $product));
        preg_match('/<meta name="description" content="([^"]*)"/', $response->getContent(), $matches);

        $this->assertArrayHasKey(1, $matches);
        $this->assertLessThanOrEqual(160, mb_strlen(html_entity_decode($matches[1])));
        $this->assertStringEndsWith('…', html_entity_decode($matches[1]));
        $this->assertStringNotContainsString('omegaunic', $matches[1]);
        $this->assertMatchesRegularExpression(
            '#<meta property="og:image" content="https?://[^"]+/storage/site/share.png">#',
            $response->getContent(),
        );
    }

    public function test_category_description_is_canonical_without_filters(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
            'description' => 'Centrale termice pentru locuințe.',
        ]);

        $response = $this->get(route('category', $category).'?pret=100');

        $response->assertSee('<meta name="description" content="Centrale termice pentru locuințe.">', false);
        $response->assertSee('<link rel="canonical" href="'.route('category', $category).'">', false);
    }

    public function test_news_page_is_an_article_for_sharing(): void
    {
        $news = News::query()->create([
            'title' => 'Garantie extinsa',
            'slug' => 'garantie-extinsa',
            'excerpt' => 'Extindem garantia la centrale.',
            'image' => 'stiri/garantie.jpg',
            'is_published' => true,
        ]);

        $response = $this->get(route('news', $news));

        $response->assertSee('<title>Garantie extinsa - DDS Services Group</title>', false);
        $response->assertSee('<meta name="description" content="Extindem garantia la centrale.">', false);
        $response->assertSee('<meta property="og:type" content="article">', false);
        $this->assertMatchesRegularExpression(
            '#<meta property="og:image" content="https?://[^"]+/storage/stiri/garantie.jpg">#',
            $response->getContent(),
        );
    }

    public function test_service_page_uses_the_first_text_and_image_block(): void
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Service',
            'slug' => 'service-seo',
        ]);
        $service = Service::query()->create([
            'service_category_id' => $category->id,
            'name' => 'Montaj centrala',
            'slug' => 'montaj-centrala-seo',
            'is_published' => true,
            'blocks' => [
                ['type' => 'image', 'data' => ['path' => 'blocuri/montaj.jpg']],
                ['type' => 'text', 'data' => ['body' => 'Montăm centrale termice în Constanța.']],
            ],
        ]);

        $response = $this->get(route('service', $service));

        $response->assertSee('<meta name="description" content="Montăm centrale termice în Constanța.">', false);
        $this->assertMatchesRegularExpression(
            '#<meta property="og:image" content="https?://[^"]+/storage/blocuri/montaj.jpg">#',
            $response->getContent(),
        );
    }

    public function test_search_keeps_the_query_in_the_canonical_url(): void
    {
        $response = $this->get(route('search', ['s' => 'Buderus']));

        $response->assertSee('<meta name="description" content="Rezultate pentru Buderus.">', false);
        $response->assertSee('<link rel="canonical" href="'.route('search', ['s' => 'Buderus']).'">', false);
    }

    public function test_login_and_account_are_not_indexed_or_shared(): void
    {
        $this->get(route('login'))
            ->assertSee('<meta name="robots" content="noindex">', false)
            ->assertDontSee('property="og:', false);

        $this->actingAs(User::factory()->create())
            ->get(route('account.home'))
            ->assertSee('<meta name="robots" content="noindex">', false)
            ->assertDontSee('property="og:', false);
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

    public function test_product_page_shows_every_gallery_image(): void
    {
        $product = Product::query()->create([
            'name' => 'Centrala cu poze',
            'slug' => 'centrala-cu-poze',
            'image' => 'produse/coperta.jpg',
            'is_published' => true,
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => 'produse/coperta.jpg',
            'sort' => 0,
        ]);
        ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => 'produse/detaliu.jpg',
            'sort' => 1,
        ]);

        $response = $this->get(route('product', $product))
            ->assertOk()
            ->assertSee('produse/coperta.jpg', false)
            ->assertSee('produse/detaliu.jpg', false)
            ->assertSee('Mărește fotografia', false)
            ->assertSee('data-product-view', false);
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
            ->assertSee('Descriere', false)
            ->assertSee('\u003Cscript\u003E', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_product_page_prefers_the_description_over_the_excerpt(): void
    {
        $product = Product::query()->create([
            'name' => 'Centrala completa',
            'slug' => 'centrala-completa',
            'excerpt' => 'Extras unic.',
            'description' => 'Descriere unica.',
            'is_published' => true,
        ]);

        $this->get(route('product', $product))
            ->assertSee('"description":"Descriere unica."', false);
    }

    public function test_product_page_uses_the_excerpt_when_the_description_is_empty(): void
    {
        $product = Product::query()->create([
            'name' => 'Centrala doar extras',
            'slug' => 'centrala-doar-extras',
            'excerpt' => 'Extras de rezerva.',
            'is_published' => true,
        ]);

        $this->get(route('product', $product))
            ->assertSee('"description":"Extras de rezerva."', false);
    }

    public function test_product_page_uses_the_global_vat_rate_when_the_product_has_none(): void
    {
        SiteSetting::set('vat_rate', 19);
        SiteSetting::set('prices_include_vat', true);

        $product = Product::query()->create([
            'name' => 'Centrala cu pret',
            'slug' => 'centrala-cu-pret',
            'price' => 119,
            'quantity' => 3,
            'is_published' => true,
        ]);

        $this->get(route('product', $product))
            ->assertSee('119,00 lei')
            ->assertSee('100,00 lei')
            ->assertSee('TVA 19%')
            ->assertSee('Stoc: 3');
    }

    public function test_product_page_keeps_a_zero_vat_rate_instead_of_the_global_one(): void
    {
        SiteSetting::set('vat_rate', 19);
        SiteSetting::set('prices_include_vat', true);

        $product = Product::query()->create([
            'name' => 'Centrala fara tva',
            'slug' => 'centrala-fara-tva',
            'price' => 100,
            'vat_rate' => 0,
            'is_published' => true,
        ]);

        $this->get(route('product', $product))
            ->assertSee('100,00 lei')
            ->assertSee('TVA 0%')
            ->assertDontSee('TVA 19%');
    }

    public function test_product_page_hides_price_and_stock_when_they_are_empty(): void
    {
        $product = Product::query()->create([
            'name' => 'Centrala fara pret',
            'slug' => 'centrala-fara-pret',
            'is_published' => true,
        ]);

        $this->get(route('product', $product))
            ->assertDontSee('lei')
            ->assertDontSee('Stoc');
    }

    public function test_product_page_shows_out_of_stock_when_quantity_is_zero(): void
    {
        $product = Product::query()->create([
            'name' => 'Centrala epuizata',
            'slug' => 'centrala-epuizata',
            'quantity' => 0,
            'is_published' => true,
        ]);

        $this->get(route('product', $product))->assertSee('Stoc epuizat');
    }

    public function test_product_page_escapes_characteristic_text(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        $attribute = Attribute::factory()->create([
            'name' => 'Observatii',
        ]);
        $category->attributes()->attach($attribute);
        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala text',
            'slug' => 'centrala-text',
            'is_published' => true,
        ]);
        $product->values()->create([
            'attribute_id' => $attribute->id,
            'value_text' => '<script>alert(1)</script>',
        ]);

        $this->get(route('product', $product))
            ->assertSee('Observatii')
            ->assertSee('Caracteristici', false)
            ->assertSee('\u003Cscript\u003E', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_category_page_shows_the_description(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
            'description' => 'Centrale pentru apartament.',
        ]);

        $this->get(route('category', $category))
            ->assertSee('Centrale pentru apartament.');
    }

    public function test_category_manufacturer_filter_keeps_the_matching_product_and_shows_counts(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        $buderus = Manufacturer::factory()->create(['name' => 'BUDERUS']);
        $ariston = Manufacturer::factory()->create(['name' => 'ARISTON']);
        Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala Buderus',
            'slug' => 'centrala-buderus',
            'manufacturer_id' => $buderus->id,
            'is_published' => true,
        ]);
        Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala Ariston',
            'slug' => 'centrala-ariston',
            'manufacturer_id' => $ariston->id,
            'is_published' => true,
        ]);

        $this->get(route('category', $category))
            ->assertSee('tracking-tight text-[#444444]">Centrale', false)
            ->assertSee('Afișează')
            ->assertDontSee('Centrale (2)')
            ->assertSee('BUDERUS (1)', false)
            ->assertSee('ARISTON (1)', false)
            ->assertDontSee('PRODUCATORI', false);

        $this->get(route('category', [
            'productCategory' => $category,
            'producator' => ['BUDERUS'],
        ]))
            ->assertDontSee('Centrale (1)')
            ->assertSee('Centrala Buderus')
            ->assertDontSee('Centrala Ariston')
            ->assertSee('ARISTON (1)', false);
    }

    public function test_child_category_lists_its_parent_and_bolds_the_open_subcategory_above_filters(): void
    {
        $parent = ProductCategory::query()->create([
            'name' => 'Echipamente',
            'slug' => 'echipamente',
        ]);
        $child = ProductCategory::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Centrale gaz',
            'slug' => 'centrale-gaz',
            'sort' => 1,
        ]);
        ProductCategory::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Pompe ramase',
            'slug' => 'pompe-ramase',
            'sort' => 2,
        ]);
        ProductCategory::query()->create([
            'name' => 'Alta ramura',
            'slug' => 'alta-ramura',
        ]);
        $buderus = Manufacturer::factory()->create(['name' => 'BUDERUS']);
        Product::query()->create([
            'product_category_id' => $child->id,
            'name' => 'Centrala Buderus',
            'slug' => 'centrala-buderus',
            'manufacturer_id' => $buderus->id,
            'is_published' => true,
        ]);

        $this->get(route('category', $child))
            ->assertSee('Subcategorii', false)
            ->assertSee('tracking-tight text-[#444444]">Centrale gaz', false)
            ->assertDontSee('Centrale gaz (1)')
            ->assertSee('Afișează')
            ->assertSeeInOrder([
                'Echipamente',
                'font-bold text-[#444444]',
                'Centrale gaz',
                'Pompe ramase',
                'BUDERUS (1)',
            ], false)
            ->assertDontSee('Alta ramura')
            ->assertDontSee('>Categorii<', false);
    }

    public function test_child_category_without_filters_lists_its_parent_and_siblings(): void
    {
        $parent = ProductCategory::query()->create([
            'name' => 'Echipamente',
            'slug' => 'echipamente',
        ]);
        $child = ProductCategory::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Centrale goale',
            'slug' => 'centrale-goale',
        ]);
        ProductCategory::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Pompe ramase',
            'slug' => 'pompe-ramase',
        ]);
        Product::query()->create([
            'product_category_id' => $child->id,
            'name' => 'Centrala simpla',
            'slug' => 'centrala-simpla',
            'is_published' => true,
        ]);

        $this->get(route('category', $child))
            ->assertSee('Subcategorii', false)
            ->assertSee('Echipamente')
            ->assertSee('font-bold text-[#444444]', false)
            ->assertSee('Centrale goale')
            ->assertSee('Pompe ramase')
            ->assertSee('Centrala simpla')
            ->assertDontSee('Producător', false);
    }

    public function test_parent_category_keeps_every_category_and_marks_only_the_open_one(): void
    {
        $parent = ProductCategory::query()->create([
            'name' => 'Echipamente',
            'slug' => 'echipamente',
            'sort' => 1,
        ]);
        $child = ProductCategory::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Pompe ramase',
            'slug' => 'pompe-ramase',
        ]);
        $other = ProductCategory::query()->create([
            'name' => 'Alta ramura',
            'slug' => 'alta-ramura',
            'sort' => 2,
        ]);
        ProductCategory::query()->create([
            'parent_id' => $other->id,
            'name' => 'Copil alta',
            'slug' => 'copil-alta',
        ]);
        $power = Attribute::factory()->ofType(AttributeType::Number)->create([
            'name' => 'Putere ascunsa',
            'unit' => 'kW',
            'is_filterable' => true,
        ]);
        $child->attributes()->attach($power);
        $buderus = Manufacturer::factory()->create(['name' => 'BUDERUS']);
        $product = Product::query()->create([
            'product_category_id' => $child->id,
            'name' => 'Centrala Buderus',
            'slug' => 'centrala-buderus',
            'manufacturer_id' => $buderus->id,
            'is_published' => true,
        ]);
        $product->values()->create([
            'attribute_id' => $power->id,
            'value_number' => 24,
        ]);

        $response = $this->get(route('category', $parent));

        $response
            ->assertSee('>Categorii<', false)
            ->assertDontSee('>Subcategorii<', false)
            ->assertSeeInOrder([
                'font-bold text-[#444444]',
                'Echipamente',
                'Pompe ramase',
                'Alta ramura',
                'Copil alta',
                'Centrala Buderus',
            ], false)
            ->assertDontSee('BUDERUS (1)', false)
            ->assertDontSee('24 kW (1)', false);

        $this->assertSame(1, substr_count($response->getContent(), 'font-bold text-[#444444]'));
    }

    public function test_category_price_and_stock_filters_limit_the_products(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala in stoc',
            'slug' => 'centrala-in-stoc',
            'price' => 100,
            'quantity' => 2,
            'is_published' => true,
        ]);
        Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala epuizata',
            'slug' => 'centrala-epuizata',
            'price' => 400,
            'quantity' => 0,
            'is_published' => true,
        ]);
        Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala fara pret',
            'slug' => 'centrala-fara-pret',
            'is_published' => true,
        ]);

        $this->get(route('category', [
            'productCategory' => $category,
            'pret' => ['min' => '50', 'max' => '150'],
        ]))
            ->assertSee('Centrala in stoc')
            ->assertDontSee('Centrala epuizata')
            ->assertDontSee('Centrala fara pret');

        $this->get(route('category', [
            'productCategory' => $category,
            'stoc' => '1',
        ]))
            ->assertSee('Centrala in stoc')
            ->assertDontSee('Centrala epuizata')
            ->assertDontSee('Centrala fara pret');
    }

    public function test_category_filter_keeps_the_matching_published_product(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        $other = ProductCategory::query()->create([
            'name' => 'Pompe',
            'slug' => 'pompe',
        ]);
        $attribute = Attribute::factory()->ofType(AttributeType::Multiselect)->create([
            'name' => 'Tip',
            'is_filterable' => true,
        ]);
        $category->attributes()->attach($attribute);
        $match = AttributeOption::factory()->create([
            'attribute_id' => $attribute->id,
            'label' => 'Condensatie',
        ]);
        $otherOption = AttributeOption::factory()->create([
            'attribute_id' => $attribute->id,
            'label' => 'Clasica',
        ]);

        $kept = Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala potrivita',
            'slug' => 'centrala-potrivita',
            'is_published' => true,
        ]);
        $kept->selectedOptions()->attach($match);

        $skipped = Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala clasica',
            'slug' => 'centrala-clasica',
            'is_published' => true,
        ]);
        $skipped->selectedOptions()->attach($otherOption);

        $hidden = Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala nepublicata',
            'slug' => 'centrala-nepublicata',
            'is_published' => false,
        ]);
        $hidden->selectedOptions()->attach($match);

        $elsewhere = Product::query()->create([
            'product_category_id' => $other->id,
            'name' => 'Pompa de caldura',
            'slug' => 'pompa-filtrata',
            'is_published' => true,
        ]);
        $elsewhere->selectedOptions()->attach($match);

        $this->get(route('category', [
            'productCategory' => $category,
            'c' => [$attribute->slug => [$match->slug]],
        ]))
            ->assertSee('Centrala potrivita')
            ->assertDontSee('Centrala clasica')
            ->assertDontSee('Centrala nepublicata')
            ->assertDontSee('Pompa de caldura');
    }

    public function test_category_requires_every_selected_characteristic(): void
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
        $power = Attribute::factory()->ofType(AttributeType::Number)->create([
            'name' => 'Putere',
            'unit' => 'kW',
            'is_filterable' => true,
        ]);
        $wifi = Attribute::factory()->ofType(AttributeType::Boolean)->create([
            'name' => 'Wi-Fi',
            'is_filterable' => true,
        ]);
        $child->attributes()->attach([$power->id, $wifi->id]);

        $match = Product::query()->create([
            'product_category_id' => $child->id,
            'name' => 'Centrala 24',
            'slug' => 'centrala-24',
            'is_published' => true,
        ]);
        $match->values()->create([
            'attribute_id' => $power->id,
            'value_number' => 24,
        ]);
        $match->values()->create([
            'attribute_id' => $wifi->id,
            'value_boolean' => true,
        ]);

        $low = Product::query()->create([
            'product_category_id' => $child->id,
            'name' => 'Centrala 12',
            'slug' => 'centrala-12',
            'is_published' => true,
        ]);
        $low->values()->create([
            'attribute_id' => $power->id,
            'value_number' => 12,
        ]);
        $low->values()->create([
            'attribute_id' => $wifi->id,
            'value_boolean' => true,
        ]);

        $this->get(route('category', [
            'productCategory' => $child,
            'c' => [
                $power->slug => ['24'],
                $wifi->slug => ['da'],
            ],
        ]))
            ->assertSee('Centrala 24')
            ->assertDontSee('Centrala 12');
    }

    public function test_category_hides_values_that_match_no_product_after_the_other_filters(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        $power = Attribute::factory()->ofType(AttributeType::Number)->create([
            'name' => 'Putere',
            'unit' => 'kW',
            'is_filterable' => true,
        ]);
        $wifi = Attribute::factory()->ofType(AttributeType::Boolean)->create([
            'name' => 'Wi-Fi',
            'is_filterable' => true,
        ]);
        $note = Attribute::factory()->create([
            'name' => 'Observatii',
            'is_filterable' => false,
        ]);
        $category->attributes()->attach([$power->id, $wifi->id, $note->id]);

        $match = Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala 24',
            'slug' => 'centrala-24',
            'is_published' => true,
        ]);
        $match->values()->create([
            'attribute_id' => $power->id,
            'value_number' => 24,
        ]);
        $match->values()->create([
            'attribute_id' => $wifi->id,
            'value_boolean' => true,
        ]);
        $match->values()->create([
            'attribute_id' => $note->id,
            'value_text' => 'SecretSpec',
        ]);

        $other = Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala 12',
            'slug' => 'centrala-12',
            'is_published' => true,
        ]);
        $other->values()->create([
            'attribute_id' => $power->id,
            'value_number' => 12,
        ]);
        $other->values()->create([
            'attribute_id' => $wifi->id,
            'value_boolean' => false,
        ]);

        $this->get(route('category', [
            'productCategory' => $category,
            'c' => [$wifi->slug => ['da']],
        ]))
            ->assertSee('Centrala 24')
            ->assertSee('24 kW (1)', false)
            ->assertSee('Putere: 24 kW', false)
            ->assertDontSee('Centrala 12')
            ->assertDontSee('12 kW', false)
            ->assertDontSee('SecretSpec', false)
            ->assertDontSee('Observatii', false);
    }

    public function test_unknown_characteristic_filter_does_not_hide_products(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala vizibila',
            'slug' => 'centrala-vizibila',
            'is_published' => true,
        ]);

        $this->get(route('category', [
            'productCategory' => $category,
            'c' => ['inexistent' => ['%']],
        ]))->assertSee('Centrala vizibila');
    }

    public function test_product_page_adds_vat_when_prices_are_entered_without_it(): void
    {
        SiteSetting::set('vat_rate', 21);
        SiteSetting::set('prices_include_vat', false);

        $product = Product::query()->create([
            'name' => 'Centrala neta',
            'slug' => 'centrala-neta',
            'price' => 100,
            'is_published' => true,
        ]);

        $this->get(route('product', $product))
            ->assertSee('121,00 lei')
            ->assertSee('100,00 lei');
    }

    public function test_category_text_filter_matches_the_exact_value(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        $attribute = Attribute::factory()->create([
            'name' => 'Observatii',
            'is_filterable' => true,
        ]);
        $category->attributes()->attach($attribute);

        $match = Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala inox',
            'slug' => 'centrala-inox',
            'is_published' => true,
        ]);
        $match->values()->create([
            'attribute_id' => $attribute->id,
            'value_text' => 'Inox',
        ]);

        $other = Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala aluminiu',
            'slug' => 'centrala-aluminiu',
            'is_published' => true,
        ]);
        $other->values()->create([
            'attribute_id' => $attribute->id,
            'value_text' => 'Aluminiu',
        ]);

        $this->get(route('category', [
            'productCategory' => $category,
            'c' => [$attribute->slug => ['Inox']],
        ]))
            ->assertSee('Centrala inox')
            ->assertSee('Inox (1)', false)
            ->assertDontSee('Centrala aluminiu');

        $this->get(route('category', [
            'productCategory' => $category,
            'c' => [$attribute->slug => ['Ino']],
        ]))
            ->assertDontSee('Centrala inox')
            ->assertDontSee('Centrala aluminiu');
    }

    public function test_catalog_layout_choice_is_remembered_in_a_cookie(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala vizibila',
            'slug' => 'centrala-vizibila',
            'is_published' => true,
        ]);

        $this->get(route('category', $category))
            ->assertSee('data-catalog-layout="grid"', false)
            ->assertDontSee('Mai multe produse din', false);

        $this->get(route('category', [
            'productCategory' => $category,
            'afisare' => 'altceva',
        ]))
            ->assertSee('data-catalog-layout="grid"', false);

        $this->get(route('category', [
            'productCategory' => $category,
            'afisare' => 'lista',
        ]))
            ->assertCookie('catalog_layout', 'list')
            ->assertSee('data-catalog-layout="list"', false);

        $this->withCookie('catalog_layout', 'list')
            ->get(route('category', $category))
            ->assertSee('data-catalog-layout="list"', false);

        $this->get(route('search', [
            's' => 'Centrala',
            'afisare' => 'grila',
        ]))
            ->assertCookie('catalog_layout', 'grid')
            ->assertSee('data-catalog-layout="grid"', false);
    }

    public function test_home_shows_a_manufacturer_logo_that_links_to_its_products(): void
    {
        Manufacturer::factory()->create([
            'name' => 'BUDERUS',
            'logo' => 'producatori/buderus.jpg',
        ]);
        Manufacturer::factory()->create([
            'name' => 'Fara logo',
        ]);

        $this->get(route('home'))
            ->assertSee('producatori/buderus.jpg', false)
            ->assertSee(route('manufacturer', 'buderus'), false)
            ->assertDontSee('Fara logo');
    }

    public function test_manufacturer_page_lists_only_that_makers_published_products(): void
    {
        $buderus = Manufacturer::factory()->create(['name' => 'BUDERUS']);
        $ariston = Manufacturer::factory()->create(['name' => 'ARISTON']);
        Product::query()->create([
            'name' => 'Centrala Buderus',
            'slug' => 'centrala-buderus',
            'manufacturer_id' => $buderus->id,
            'is_published' => true,
        ]);
        Product::query()->create([
            'name' => 'Centrala Ariston',
            'slug' => 'centrala-ariston',
            'manufacturer_id' => $ariston->id,
            'is_published' => true,
        ]);
        Product::query()->create([
            'name' => 'Centrala Buderus ascunsa',
            'slug' => 'centrala-buderus-ascunsa',
            'manufacturer_id' => $buderus->id,
            'is_published' => false,
        ]);

        $this->get(route('manufacturer', $buderus))
            ->assertSee('Centrala Buderus')
            ->assertSee('<link rel="canonical" href="'.route('manufacturer', 'buderus').'">', false)
            ->assertSee('href="'.route('manufacturer', 'buderus').'"', false)
            ->assertDontSee('Centrala Ariston')
            ->assertDontSee('Centrala Buderus ascunsa');
    }

    public function test_old_manufacturer_search_redirects_to_the_manufacturer_page(): void
    {
        Manufacturer::factory()->create(['name' => 'BUDERUS']);

        $this->get(route('search', ['producator' => 'BUDERUS']))
            ->assertStatus(301)
            ->assertRedirectToRoute('manufacturer', 'buderus');
    }

    public function test_unknown_manufacturer_search_returns_not_found(): void
    {
        $this->get(route('search', ['producator' => 'Inexistent']))
            ->assertNotFound();
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
