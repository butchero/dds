<?php

namespace Tests\Feature;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SiteSetting;
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

        $this->get(route('product', $product))
            ->assertOk()
            ->assertSee('produse/coperta.jpg', false)
            ->assertSee('produse/detaliu.jpg', false)
            ->assertSee('data-gallery-thumb', false);
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
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
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
        Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala Buderus',
            'slug' => 'centrala-buderus',
            'manufacturer' => 'BUDERUS',
            'is_published' => true,
        ]);
        Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala Ariston',
            'slug' => 'centrala-ariston',
            'manufacturer' => 'ARISTON',
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
        Product::query()->create([
            'product_category_id' => $child->id,
            'name' => 'Centrala Buderus',
            'slug' => 'centrala-buderus',
            'manufacturer' => 'BUDERUS',
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
        $product = Product::query()->create([
            'product_category_id' => $child->id,
            'name' => 'Centrala Buderus',
            'slug' => 'centrala-buderus',
            'manufacturer' => 'BUDERUS',
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
            ->assertCookie('dds_catalog_layout', 'list')
            ->assertSee('data-catalog-layout="list"', false);

        $this->withCookie('dds_catalog_layout', 'list')
            ->get(route('category', $category))
            ->assertSee('data-catalog-layout="list"', false);

        $this->get(route('search', [
            's' => 'Centrala',
            'afisare' => 'grila',
        ]))
            ->assertCookie('dds_catalog_layout', 'grid')
            ->assertSee('data-catalog-layout="grid"', false);
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
