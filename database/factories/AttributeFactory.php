<?php

namespace Database\Factories;

use App\Enums\AttributeType;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'type' => AttributeType::Text,
            'is_filterable' => false,
            'sort' => 0,
        ];
    }

    public function ofType(AttributeType $type): static
    {
        return $this->state([
            'type' => $type,
            'is_filterable' => $type->filtersByDefault(),
        ]);
    }
}
