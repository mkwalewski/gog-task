<?php

namespace App\Api\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\DTO\CartItemInput;
use App\Api\DTO\CartItemOutput;

#[ApiResource(
    collectionOperations: [
        'post' => [
            'openapi_context' => [
                'parameters' => [
                    [
                        'name' => 'cartId',
                        'in' => 'query',
                        'description' => 'Cart id',
                        'type' => 'string',
                        'required' => true,
                    ],
                    [
                        'name' => 'productId',
                        'in' => 'query',
                        'description' => 'Product id',
                        'type' => 'string',
                        'required' => true,
                    ],
                    [
                        'name' => 'quantity',
                        'in' => 'query',
                        'description' => 'Quantity',
                        'type' => 'string',
                        'required' => true,
                    ],
                ]
            ]
        ]
    ],
    itemOperations: [],
    input: CartItemInput::class,
    output: CartItemOutput::class
)]
class CartItem
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public int $cartId = 0,
    ) {
    }
}