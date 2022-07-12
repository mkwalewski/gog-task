<?php

namespace App\Api\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Api\Resource\Cart;
use App\Service\CartService;

final class CartPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private CartService $cartService
    ) {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Cart;
    }

    public function persist($data, array $context = [])
    {
        $cartId = $this->cartService->createCart();
        $cart = $this->cartService->getCart($cartId);

        return $cart;
    }

    public function remove($data, array $context = [])
    {
    }
}