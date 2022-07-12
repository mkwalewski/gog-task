<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\DataFixtures\ProductFixtures;
use App\Entity\Product;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

class ProductTest extends ApiTestCase
{
    private Client $client;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->client = static::createClient();

        $purger = new ORMPurger($this->getEntityManager());
        $purger->purge();

        $fixture = new ProductFixtures();
        $fixture->load($this->getEntityManager());
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }

    public function testItGetProducts(): void
    {
        $response = $this->client->request('GET', '/api/products');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertSame(5, $data['hydra:totalItems']);
        $this->assertCount(3, $data['hydra:member']);
    }

    public function testItGetSingleProduct(): void
    {
        $product = $this->prepareProduct('Cyberpunk', '6.99', true);
        $response = $this->client->request('GET',
            sprintf('/api/products/%s', $product->getId()),
            [
                'headers' => ['Accept' => 'application/json'],
            ]
        );

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertSame($product->getId(), $data['id']);
        $this->assertSame($product->getTitle(), $data['title']);
        $this->assertSame($product->getPrice(), $data['price']);

        $response = $this->client->request('GET', '/api/products');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertSame(6, $data['hydra:totalItems']);
    }

    public function testItCreateProduct(): void
    {
        $product = $this->prepareProduct('Cyberpunk', '6.99');
        $response = $this->client->request('POST', '/api/products', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'title' => $product->getTitle(),
                'price' => $product->getPrice()
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertSame($product->getTitle(), $data['title']);
        $this->assertSame($product->getPrice(), $data['price']);

        $response = $this->client->request('GET', '/api/products');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertSame(6, $data['hydra:totalItems']);
    }

    public function testItUpdateProduct(): void
    {
        $product = $this->prepareProduct('Cyberpunk', '6.99', true);
        $response = $this->client->request('PUT',
            sprintf('/api/products/%s', $product->getId()),
            [
                'headers' => ['Accept' => 'application/json'],
                'json' => [
                    'title' => 'Cyberpunk 2077',
                    'price' => '69.90'
                ],
            ]
        );

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertSame('Cyberpunk 2077', $data['title']);
        $this->assertSame('69.90', $data['price']);

        $response = $this->client->request('GET', '/api/products');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertSame(6, $data['hydra:totalItems']);
    }

    public function testItDeleteProduct(): void
    {
        $product = $this->prepareProduct('Cyberpunk', '6.99', true);
        $response = $this->client->request('DELETE',
            sprintf('/api/products/%s', $product->getId()),
            [
                'headers' => ['Accept' => 'application/json'],
            ]
        );

        $this->assertResponseIsSuccessful();

        $response = $this->client->request('GET', '/api/products');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertSame(5, $data['hydra:totalItems']);
    }

    public function testItTitleIsUnique(): void
    {
        $product = $this->prepareProduct('Cyberpunk', '6.99', true);
        $response = $this->client->request('POST', '/api/products', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'title' => $product->getTitle(),
                'price' => $product->getPrice()
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);

        $response = $this->client->request('GET', '/api/products');

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertSame(6, $data['hydra:totalItems']);
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
