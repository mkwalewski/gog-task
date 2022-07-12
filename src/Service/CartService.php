<?php

namespace App\Service;

use App\Api\DTO\CartItemOutput;
use App\Entity\Cart as CartEntity;
use App\Entity\CartItem as CartItemEntity;
use App\Entity\Product as ProductEntity;
use App\Api\Resource\Cart as CartResource;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;

class CartService
{
    public function __construct(
        private CartRepository $cartRepository,
        private CartItemRepository $cartItemRepository,
        private ProductRepository $productRepository
    ) {
    }

    public function createCart(): int
    {
        $cart = new CartEntity();
        $cart->setTotal(0);
        $this->cartRepository->save($cart, true);

        return $cart->getId();
    }

    private function createCartItem(CartEntity $cart, ProductEntity $product, int $quantity): CartItemEntity
    {
        $items = $this->cartItemRepository->findByCartId($cart->getId());

        if (count($items) === 3) {
            throw new \Exception("You can't add more than 3 products to cart");
        }

        $cartItem = new CartItemEntity();
        $cartItem->setCart($cart);
        $cartItem->setProduct($product);
        $cartItem->setTitle($product->getTitle());
        $cartItem->setPrice($product->getPrice());
        $cartItem->setQuantity($quantity);

        return $cartItem;
    }

    public function updateCart(int $cartId, int $productId, int $quantity): void
    {
        $cart = $this->cartRepository->find($cartId);
        $product = $this->productRepository->find($productId);

        if (!$cart) {
            throw new \Exception('Cart not fonud');
        }

        if (!$product) {
            throw new \Exception('Product not fonud');
        }

        $cartItem = $this->cartItemRepository->findOneByCartIdAndProductId($cartId, $productId);

        if ($cartItem && $quantity === 0) {
            $this->cartItemRepository->remove($cartItem, true);
        }

        if ($cartItem && $quantity > 0) {
            $cartItem = $this->addQuantity($cartItem, $quantity);
            $cartItem = $this->checkQuantity($cartItem);
            $this->cartItemRepository->save($cartItem, true);
        }

        if (!$cartItem && $quantity > 0) {
            $cartItem = $this->createCartItem($cart, $product, $quantity);
            $cartItem = $this->checkQuantity($cartItem);
            $this->cartItemRepository->save($cartItem, true);
        }

        $this->updateTotal($cart);
    }

    private function addQuantity(CartItemEntity $cartItem, int $quantity): CartItemEntity
    {
        $newQuantity = $cartItem->getQuantity() + $quantity;
        $cartItem->setQuantity($newQuantity);

        return  $cartItem;
    }

    private function checkQuantity(CartItemEntity $cartItem): CartItemEntity
    {
        if ($cartItem->getQuantity() > 10) {
            throw new \Exception("You can't add more than 10 quantity of the same product");
        }

        return  $cartItem;
    }

    private function updateTotal(CartEntity $cart): void
    {
        $total = 0;
        $items = $this->cartItemRepository->findByCartId($cart->getId());

        foreach ($items as $item) {
            $total += $item->getPrice() * $item->getQuantity();
        }

        $cart->setTotal($total);
        $this->cartRepository->save($cart, true);
    }

    public function getCart(int $cartId): CartResource
    {
        $cart = $this->cartRepository->find($cartId);

        if (!$cart) {
            throw new \Exception('Cart not fonud');
        }

        $cartResource = new CartResource(
            $cart->getId(),
            $cart->getTotal(),
            $this->getCartItems($cartId)
        );

        return $cartResource;
    }

    private function getCartItems(int $cartId): array
    {
        $items = [];
        $cartItems = $this->cartItemRepository->findByCartId($cartId);

        foreach ($cartItems as $item) {
            $items[] = new CartItemOutput(
                $item->getProduct()->getId(),
                $item->getTitle(),
                $item->getPrice(),
                $item->getQuantity()
            );
        }

        return $items;
    }
}