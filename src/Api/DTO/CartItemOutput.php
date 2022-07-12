<?php

namespace App\Api\DTO;

class CartItemOutput
{
    public function __construct(
        public int $productId,
        public string $title,
        public string $price,
        public int $quantity
    ) {
    }
}