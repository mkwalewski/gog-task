<?php

namespace App\Api\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

#[ApiResource(
    collectionOperations: [
        'post' => [],
    ],
    itemOperations: [
        'get' => [],
    ]
)]
class Cart
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public int $id = 0,
        public float $total = 0,
        public array $items = []
    ) {
    }
}