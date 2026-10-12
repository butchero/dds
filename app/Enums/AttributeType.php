<?php

namespace App\Enums;

enum AttributeType: string
{
    case Text = 'text';
    case Number = 'number';
    case Boolean = 'boolean';
    case Select = 'select';
    case Multiselect = 'multiselect';

    public function label(): string
    {
        return match ($this) {
            self::Text => __('Text'),
            self::Number => __('Număr'),
            self::Boolean => __('Da / Nu'),
            self::Select => __('Listă'),
            self::Multiselect => __('Listă multiplă'),
        };
    }

    public function hasOptions(): bool
    {
        return $this === self::Select || $this === self::Multiselect;
    }

    public function filtersByDefault(): bool
    {
        return false;
    }
}
