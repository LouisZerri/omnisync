<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Construit la requête de listing des produits, triés par date de création récente.
     * Retourne un QueryBuilder (non exécuté) pour que le paginator puisse l'exploiter.
     */
    public function createListQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('product')
            ->orderBy('product.id', 'DESC');
    }

    /**
     * Nombre total de produits au catalogue.
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('product')
            ->select('COUNT(product.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Valeur du catalogue en centimes : somme de (prix × stock) sur tous les produits.
     */
    public function sumCatalogueValueCents(): int
    {
        return (int) $this->createQueryBuilder('product')
            ->select('COALESCE(SUM(product.priceCents * product.stock), 0)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nombre de produits en rupture (stock à zéro).
     */
    public function countOutOfStock(): int
    {
        return (int) $this->createQueryBuilder('product')
            ->select('COUNT(product.id)')
            ->where('product.stock = 0')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Produits dont le stock est sous le seuil (rupture incluse), les plus bas d'abord.
     *
     * @return Product[]
     */
    public function findLowStock(int $threshold, int $limit): array
    {
        return $this->createQueryBuilder('product')
            ->where('product.stock <= :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('product.stock', 'ASC')
            ->addOrderBy('product.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
