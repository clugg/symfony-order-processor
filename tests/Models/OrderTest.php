<?php

namespace App\Tests\Models;

use App\Models\Discount;
use App\Models\Item;
use App\Models\Order;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OrderTest extends KernelTestCase
{
    protected const BASE_INPUT = [
        'order_id' => 1234,
        'order_date' => 'Fri, 08 Mar 2019 12:13:29 +0000',
        'customer' => [
            'shipping_address' => [
                'state' => 'VICTORIA',
            ],
        ],
        'discounts' => [],
        'items' => [],
    ];

    protected const BASE_OUTPUT = [
        'order_id' => 1234,
        'order_datetime' => '2019-03-08T12:13:29+0000',
        'total_order_value' => 0.0,
        'average_unit_price' => 0.0,
        'distinct_unit_count' => 0,
        'total_units_count' => 0,
        'customer_state' => 'VICTORIA',
    ];

    protected static function extendBaseInput(array $data): array
    {
        return array_merge(self::BASE_INPUT, $data);
    }

    protected static function extendBaseOutput(array $data): array
    {
        return array_merge(self::BASE_OUTPUT, $data);
    }

    public function testItExtractsBasicData(): void
    {
        $order = Order::fromArray(self::BASE_INPUT);

        $this->assertEquals(self::BASE_OUTPUT, $order->process());
    }

    public function testItConvertsOrderDateToUTC(): void
    {
        $order = Order::fromArray(self::extendBaseInput([
            'order_date' => 'Fri, 08 Mar 2019 12:13:29 +1100',
        ]));

        $this->assertEquals(self::extendBaseOutput([
            'order_datetime' => '2019-03-08T01:13:29+0000',
        ]), $order->process());
    }

    protected function itemsDiscountsProvider(): array
    {
        return [
            [
                [
                    ['quantity' => 1, 'unit_price' => 10.50],
                ],
                [
                    ['type' => 'DOLLAR', 'value' => 5, 'priority' => 1],
                ],
                [
                    'total_order_value' => 5.50,
                    'average_unit_price' => 10.50,
                    'distinct_unit_count' => 1,
                    'total_units_count' => 1,
                ],
            ],
            [
                [
                    ['quantity' => 2, 'unit_price' => 3.50],
                    ['quantity' => 5, 'unit_price' => 5.80],
                ],
                [],
                [
                    'total_order_value' => 36,
                    'average_unit_price' => 4.65,
                    'distinct_unit_count' => 2,
                    'total_units_count' => 7,
                ],
            ],
            [
                [
                    ['quantity' => 1, 'unit_price' => 7.50],
                    ['quantity' => 2, 'unit_price' => 50.36],
                    ['quantity' => 7, 'unit_price' => 3.80],
                ],
                [
                    ['type' => 'DOLLAR', 'value' => 5, 'priority' => 2],
                    ['type' => 'PERCENTAGE', 'value' => 10, 'priority' => 1],
                ],
                [
                    'total_order_value' => 116.34,
                    'average_unit_price' => 20.55,
                    'distinct_unit_count' => 3,
                    'total_units_count' => 10,
                ],
            ],
        ];
    }

    /**
     * @dataProvider itemsDiscountsProvider
     *
     * @param Item[] $items
     * @param Discount[] $discounts
     * @param array $values
     */
    public function testItCalculatesValuesCorrectly(array $items, array $discounts, array $values): void
    {
        $order = Order::fromArray(self::extendBaseInput(compact('items', 'discounts')));

        $this->assertEquals(self::extendBaseOutput($values), $order->process());
    }
}
