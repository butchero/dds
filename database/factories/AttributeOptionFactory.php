<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttributeOption>
 */
class AttributeOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'label' => fake()->unique()->word(),
            'sort' => 0,
        ];
    }
}
