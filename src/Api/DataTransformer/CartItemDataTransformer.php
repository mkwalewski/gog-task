<?php

namespace App\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Api\Resource\CartItem;

class CartItemDataTransformer implements DataTransformerInterface
{
    public function transform($data, string $to, array $context = []): object
    {
        $cartItem = new CartItem();
        $cartItem->cartId = $data->cartId;
        $cartItem->productId = $data->productId;
        $cartItem->quantity = $data->quantity;

        return $cartItem;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof CartItem) {
            return false;
        }

        return CartItem::class === $to && null !== ($context['input']['class'] ?? null);
    }
}
