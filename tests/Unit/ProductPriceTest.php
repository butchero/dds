<?php

namespace Tests\Unit;

use App\Support\ProductPrice;
use PHPUnit\Framework\TestCase;

class ProductPriceTest extends TestCase
{
    public function test_quote_splits_a_price_that_includes_vat(): void
    {
        $price = ProductPrice::quote('121', '21', true);

        $this->assertSame('121,00 lei', $price->gross);
        $this->assertSame('100,00 lei', $price->net);
        $this->assertSame('21%', $price->vatRate);
    }

    public function test_quote_adds_vat_when_the_price_excludes_it(): void
    {
        $price = ProductPrice::quote('100', '21', false);

        $this->assertSame('100,00 lei', $price->net);
        $this->assertSame('121,00 lei', $price->gross);
        $this->assertSame('21%', $price->vatRate);
    }

    public function test_quote_keeps_a_zero_rate(): void
    {
        $price = ProductPrice::quote('100', '0', true);

        $this->assertSame('100,00 lei', $price->gross);
        $this->assertSame('100,00 lei', $price->net);
        $this->assertSame('0%', $price->vatRate);
    }
}
