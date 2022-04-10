<?php

namespace App\Models;

use App\Enums\DiscountType;
use stdClass;

class Discount
{
    public function __construct(
        public DiscountType $type,
        public float $value,
        public int $priority,
    ) {
    }

    public static function fromArray(array|stdClass $data): self
    {
        $data = (array) $data;

        return new self(DiscountType::from($data['type']), $data['value'], $data['priority']);
    }

    /**
     * Applies the discount to the price provided.
     *
     * @param float $cents
     * @return float
     */
    public function apply(float $cents): float
    {
        return match ($this->type) {
            DiscountType::Dollar => $cents - ($this->value * 100),
            DiscountType::Percentage => $cents * (1 - ($this->value / 100)),
        };
    }
}
