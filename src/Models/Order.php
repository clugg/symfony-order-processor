<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use stdClass;

class Order
{
    /**
     * @param integer $id
     * @param CarbonInterface $date
     * @param string $customerState
     * @param Discount[] $discounts
     * @param Item[] $items
     */
    public function __construct(
        public int $id,
        public CarbonInterface $date,
        public string $customerState,
        public array $discounts,
        public array $items,
    ) {
        usort($this->discounts, fn (Discount $a, Discount $z) => $a->priority - $z->priority);
    }

    public static function fromArray(array|stdClass $data): self
    {
        $data = (array) $data;
        $customer = (array) $data['customer'] ?? [];
        $shippingAddress = (array) $customer['shipping_address'] ?? [];

        return new self(
            $data['order_id'],
            Carbon::parse($data['order_date']),
            $shippingAddress['state'] ?? '?',
            array_map(fn ($discount) => Discount::fromArray($discount), $data['discounts']),
            array_map(fn ($item) => Item::fromArray($item), $data['items']),
        );
    }

    public function process(): array
    {
        return [
            'order_id' => $this->id,
            'order_datetime' => $this->date->utc()->format(Carbon::ISO8601),
            'total_order_value' => round($this->discountedPrice() / 100, 2),
            'average_unit_price' => round($this->averageUnitPrice() / 100, 2),
            'distinct_unit_count' => count($this->items),
            'total_units_count' => $this->totalUnits(),
            'customer_state' => $this->customerState,
        ];
    }

    /**
     * Calculates the average unit price in cents.
     *
     * @return float
     */
    public function averageUnitPrice(): float
    {
        if (empty($this->items)) {
            return 0.0;
        }

        return array_reduce(
            $this->items,
            fn (int $carry, Item $item) => $carry + $item->priceInCents,
            0,
        ) / count($this->items);
    }

    /**
     * Calculates the total number of units.
     *
     * @return integer
     */
    public function totalUnits(): int
    {
        if (empty($this->items)) {
            return 0;
        }

        return array_reduce(
            $this->items,
            fn (int $carry, Item $item) => $carry + $item->quantity,
            0,
        );
    }

    /**
     * Calculates the total price in cents before any discounts.
     *
     * @return integer
     */
    public function totalPrice(): int
    {
        if (empty($this->items)) {
            return 0;
        }

        return array_reduce(
            $this->items,
            fn (int $carry, Item $item) => $carry + $item->totalPrice(),
            0,
        );
    }

    /**
     * Calculates the total price in cents after all discounts are applied.
     *
     * @return float
     */
    public function discountedPrice(): float
    {
        if (empty($this->items)) {
            return 0.0;
        }

        return array_reduce(
            $this->discounts,
            fn (float $price, Discount $discount) => $discount->apply($price),
            $this->totalPrice(),
        );
    }
}
