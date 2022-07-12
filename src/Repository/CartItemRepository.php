<?php

namespace App\Repository;

use App\Entity\CartItem;

/**
 * @extends ServiceEntityRepository<CartItem>
 *
 * @method CartItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method CartItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method CartItem[]    findAll()
 * @method CartItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartItemRepository extends BaseRepository
{
    public string $entityClass = CartItem::class;

    public function findByCartId(int $cartId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.cart = :id')
            ->setParameter('id', $cartId)
            ->getQuery()
            ->getResult();
    }

    public function findOneByCartIdAndProductId(int $cartId, int $productId): ?CartItem
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.cart = :cartId AND c.product = :productId')
            ->setParameter('cartId', $cartId)
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
