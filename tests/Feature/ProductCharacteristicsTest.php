<?php

namespace Tests\Feature;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\ProductCharacteristics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCharacteristicsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_saves_allowed_values_and_ignores_other_categories(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        $text = Attribute::factory()->create([
            'name' => 'Observatii',
        ]);
        $foreign = Attribute::factory()->create([
            'name' => 'Alta',
        ]);
        $choice = Attribute::factory()->ofType(AttributeType::Multiselect)->create([
            'name' => 'Tip',
        ]);
        $flag = Attribute::factory()->ofType(AttributeType::Boolean)->create([
            'name' => 'Wi-Fi',
        ]);
        $category->attributes()->attach([$text->id, $choice->id, $flag->id]);
        $kept = AttributeOption::factory()->create([
            'attribute_id' => $choice->id,
            'label' => 'Condensatie',
        ]);
        $also = AttributeOption::factory()->create([
            'attribute_id' => $choice->id,
            'label' => 'Clasica',
        ]);
        $foreignOption = AttributeOption::factory()->create([
            'attribute_id' => $foreign->id,
            'label' => 'Strain',
        ]);
        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala',
            'slug' => 'centrala',
            'is_published' => true,
        ]);

        ProductCharacteristics::sync($product, [
            ['attribute_id' => $text->id, 'value_text' => 'Inox'],
            ['attribute_id' => $foreign->id, 'value_text' => 'Nu se salveaza'],
            ['attribute_id' => $flag->id, 'value_boolean' => '0'],
            ['attribute_id' => $choice->id, 'option_ids' => [$kept->id, $also->id, $foreignOption->id]],
        ]);

        $this->assertSame('Inox', $product->values()->where('attribute_id', $text->id)->value('value_text'));
        $this->assertNull($product->values()->where('attribute_id', $foreign->id)->first());
        $this->assertFalse($product->values()->where('attribute_id', $flag->id)->first()->value_boolean);
        $this->assertTrue($product->selectedOptions()->whereKey($kept->id)->exists());
        $this->assertTrue($product->selectedOptions()->whereKey($also->id)->exists());
        $this->assertFalse($product->selectedOptions()->whereKey($foreignOption->id)->exists());
    }

    public function test_sync_clears_an_empty_text_value(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        $text = Attribute::factory()->create([
            'name' => 'Observatii',
        ]);
        $category->attributes()->attach($text);
        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala',
            'slug' => 'centrala',
            'is_published' => true,
        ]);

        ProductCharacteristics::sync($product, [
            ['attribute_id' => $text->id, 'value_text' => 'Inox'],
        ]);
        ProductCharacteristics::sync($product, [
            ['attribute_id' => $text->id, 'value_text' => ''],
        ]);

        $this->assertNull($product->values()->where('attribute_id', $text->id)->first());
    }

    public function test_sync_keeps_a_zero_number(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Centrale',
            'slug' => 'centrale',
        ]);
        $power = Attribute::factory()->ofType(AttributeType::Number)->create([
            'name' => 'Putere',
        ]);
        $category->attributes()->attach($power);
        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'name' => 'Centrala',
            'slug' => 'centrala',
            'is_published' => true,
        ]);

        ProductCharacteristics::sync($product, [
            ['attribute_id' => $power->id, 'value_number' => 0],
        ]);

        $this->assertSame('0.00', $product->values()->where('attribute_id', $power->id)->first()->value_number);
    }

    public function test_gallery_cover_is_the_first_image(): void
    {
        Storage::fake('public');

        $product = Product::query()->create([
            'name' => 'Centrala',
            'slug' => 'centrala',
            'is_published' => true,
        ]);

        ProductCharacteristics::syncGallery($product, [
            'produse/fata.jpg',
            'produse/spate.jpg',
        ]);

        $product->refresh();

        $this->assertSame('produse/fata.jpg', $product->image);
        $this->assertSame(['produse/fata.jpg', 'produse/spate.jpg'], $product->images()->orderBy('sort')->pluck('path')->all());
    }
}
