<?php

namespace App\Api\DTO;

class CartItemInput
{
    public function __construct(
        public int $cartId,
        public int $productId,
        public int $quantity
    ) {
    }
}