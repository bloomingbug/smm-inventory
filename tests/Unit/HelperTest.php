<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function testFormatPrice()
    {
        $price = 50000;
        $formattedPrice = formatPrice($price);
        $this->assertEquals("Rp.50.000", $formattedPrice);

        $price = 1000000;
        $formattedPrice = formatPrice($price);
        $this->assertEquals("Rp.1.000.000", $formattedPrice);
    }
}
