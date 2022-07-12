<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    private array $items = [
        ['title' => 'Fallout', 'price' => 1.99],
        ['title' => 'Don’t Starve', 'price' => 2.99],
        ['title' => 'Baldur’s Gate', 'price' => 3.99],
        ['title' => 'Icewind Dale', 'price' => 4.99],
        ['title' => 'Bloodborne', 'price' => 5.99]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->items as $item) {
            $product = new Product();
            $product->setTitle($item['title']);
            $product->setPrice($item['price']);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
