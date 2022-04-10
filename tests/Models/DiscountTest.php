<?php

namespace App\Tests\Models;

use App\Enums\DiscountType;
use App\Models\Discount;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DiscountTest extends KernelTestCase
{
    protected function percentageProvider(): array
    {
        return [
            [10, 10_00, 9_00],
            [15, 10_00, 8_50],
            [50, 10_00, 5_00],
            [100, 10_00, 0],
            [36, 150_00, 96_00],
            [42, 13_37, 7_75.46],
        ];
    }

    /**
     * @dataProvider percentageProvider
     */
    public function testItAppliesPercentageDiscount(int $percentage, int $inputCents, float $outputCents): void
    {
        $discount = Discount::fromArray([
            'type' => DiscountType::Percentage->value,
            'value' => $percentage,
            'priority' => 1,
        ]);

        $this->assertEquals($outputCents, $discount->apply($inputCents));
    }

    protected function dollarProvider(): array
    {
        return [
            [10, 10_00, 0],
            [5, 10_00, 5_00],
            [2.50, 13_37, 10_87],
            [36, 150_00, 114_00],
            [12.34, 1024_36, 1012_02],
        ];
    }

    /**
     * @dataProvider dollarProvider
     */
    public function testItAppliesDollarDiscount(float $dollars, int $inputCents, float $outputCents): void
    {
        $discount = Discount::fromArray([
            'type' => DiscountType::Dollar->value,
            'value' => $dollars,
            'priority' => 1,
        ]);

        $this->assertEquals($outputCents, $discount->apply($inputCents));
    }
}
