<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

class CartTest extends ApiTestCase
{
    private Client $client;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->client = static::createClient();

        $purger = new ORMPurger($this->getEntityManager());
        $purger->purge();
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }

    public function testItCreateCart(): void
    {
        $response = $this->client->request('POST', '/api/carts', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [],
        ]);

        $this->assertResponseStatusCodeSame(201);
    }

    public function testItAddOrUpdateItemsToCart(): void
    {
        $cart = $this->prepareCart(true);
        $product1 = $this->prepareProduct('Fallout', '1.99', true);
        $product2 = $this->prepareProduct('Bloodborne', '5.99', true);
        $product3 = $this->prepareProduct('Cyberpunk', '6.99', true);

        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product1->getId(),
                'quantity' => 1
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product1->getId(),
                'quantity' => 2
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product2->getId(),
                'quantity' => 2
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product3->getId(),
                'quantity' => 1
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertSame(24.94, $data['total']);
        $this->assertCount(3, $data['items']);
        $this->assertSame(3, $data['items'][0]['quantity']);
        $this->assertSame(2, $data['items'][1]['quantity']);
        $this->assertSame(1, $data['items'][2]['quantity']);
    }

    public function testItRemoveItemsFromCart(): void
    {
        $cart = $this->prepareCart(true);
        $product1 = $this->prepareProduct('Fallout', '1.99', true);
        $product2 = $this->prepareProduct('Bloodborne', '5.99', true);

        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product1->getId(),
                'quantity' => 1
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product2->getId(),
                'quantity' => 2
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product1->getId(),
                'quantity' => 0
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertSame(11.98, $data['total']);
        $this->assertCount(1, $data['items']);
        $this->assertSame(2, $data['items'][0]['quantity']);
    }

    public function testItThrowExceptionCantAddMoreProducts(): void
    {
        $cart = $this->prepareCart(true);
        $product1 = $this->prepareProduct('Fallout', '1.99', true);
        $product2 = $this->prepareProduct('Icewind Dale', '4.99', true);
        $product3 = $this->prepareProduct('Bloodborne', '5.99', true);
        $product4 = $this->prepareProduct('Cyberpunk', '6.99', true);

        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product1->getId(),
                'quantity' => 1
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product2->getId(),
                'quantity' => 2
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product3->getId(),
                'quantity' => 2
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $this->expectExceptionMessage("You can't add more than 3 products to cart");
        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product4->getId(),
                'quantity' => 1
            ],
        ]);
        $response->toArray();
    }

    public function testItThrowExceptionQuantityExceeded(): void
    {
        $cart = $this->prepareCart(true);
        $product1 = $this->prepareProduct('Fallout', '1.99', true);

        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product1->getId(),
                'quantity' => 5
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $this->expectExceptionMessage("You can't add more than 10 quantity of the same product");
        $response = $this->client->request('POST', '/api/cart_items', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'cartId' => $cart->getId(),
                'productId' => $product1->getId(),
                'quantity' => 6
            ],
        ]);
        $response->toArray();
    }

    public function prepareCart(bool $save = false): Cart
    {
        $cart = new Cart();
        $cart->setTotal(0);

        if ($save) {
            $this->getEntityManager()->persist($cart);
            $this->getEntityManager()->flush();
        }

        return $cart;
    }

    public function prepareProduct(string $title, string $price, bool $save = false): Product
    {
        $product = new Product();
        $product->setTitle($title);
        $product->setPrice($price);

        if ($save) {
            $this->getEntityManager()->persist($product);
            $this->getEntityManager()->flush();
        }

        return $product;
    }
}
