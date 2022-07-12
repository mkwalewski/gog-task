<?php

namespace App\Api\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Api\Resource\CartItem;
use App\Service\CartService;

final class CartItemPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private CartService $cartService
    ) {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof CartItem;
    }

    public function persist($data, array $context = [])
    {
        $this->cartService->updateCart($data->cartId, $data->productId, $data->quantity);
        $cart = $this->cartService->getCart($data->cartId);

        return $cart;
    }

    public function remove($data, array $context = [])
    {
        $this->cartService->updateCart($data->cartId, $data->productId, 0);
    }
}