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
            self::Text => 'Text',
            self::Number => 'Număr',
            self::Boolean => 'Da / Nu',
            self::Select => 'Listă',
            self::Multiselect => 'Listă multiplă',
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
