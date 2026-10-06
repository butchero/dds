<?php

namespace App\Models;

use App\Enums\AttributeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    protected $fillable = [
        'product_id', 'attribute_id', 'value_text', 'value_number', 'value_boolean',
    ];

    protected function casts(): array
    {
        return [
            'value_number' => 'decimal:2',
            'value_boolean' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function display(): ?string
    {
        $attribute = $this->attribute;

        return match ($attribute->type) {
            AttributeType::Text => filled($this->value_text) ? $this->value_text : null,
            AttributeType::Number => $this->value_number === null
                ? null
                : self::formatNumber((string) $this->value_number).($attribute->unit ? ' '.$attribute->unit : ''),
            AttributeType::Boolean => $this->value_boolean === null ? null : ($this->value_boolean ? 'Da' : 'Nu'),
            AttributeType::Select, AttributeType::Multiselect => null,
        };
    }

    private static function formatNumber(string $number): string
    {
        $trimmed = rtrim(rtrim($number, '0'), '.');

        return str_replace('.', ',', $trimmed === '' ? '0' : $trimmed);
    }
}
