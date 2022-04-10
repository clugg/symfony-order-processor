<?php

namespace App\Tests\Models;

use App\Models\Item;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ItemTest extends KernelTestCase
{
    protected function itemProvider(): array
    {
        return [
            [1, 10.00, 10_00],
            [2, 2.42, 4_84],
            [3, 13.37, 40_11],
            [100, 0.02, 2_00],
        ];
    }

    /**
     * @dataProvider itemProvider
     */
    public function testItCalculatesTotalPrice(int $quantity, float $unitDollars, int $expectedCents): void
    {
        $item = Item::fromArray([
            'quantity' => $quantity,
            'unit_price' => $unitDollars,
        ]);

        $this->assertEquals($expectedCents, $item->totalPrice());
    }
}
