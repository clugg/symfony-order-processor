<?php

namespace App\Models;

use stdClass;

class Item
{
    public function __construct(
        public int $quantity,
        public int $priceInCents,
    ) {
    }

    public static function fromArray(array|stdClass $data): self
    {
        $data = (array) $data;

        return new self($data['quantity'], floor($data['unit_price'] * 100));
    }

    /**
     * Returns the total price of a line item in cents.
     *
     * @return integer
     */
    public function totalPrice(): int
    {
        return $this->priceInCents * $this->quantity;
    }
}
