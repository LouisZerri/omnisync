<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Tests\IntegrationTestCase;

/**
 * Vérifie les agrégats qui alimentent le tableau de bord (KPI catalogue).
 */
final class ProductRepositoryTest extends IntegrationTestCase
{
    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getContainer()->get(ProductRepository::class);

        $this->createProduct('SKU-1', 1000, 5);   // valeur 5000
        $this->createProduct('SKU-2', 2000, 0);   // rupture, valeur 0
        $this->createProduct('SKU-3', 500, 10);   // valeur 5000
        $this->em->flush();
    }

    public function testCountAll(): void
    {
        self::assertSame(3, $this->repository->countAll());
    }

    public function testCatalogueValueIsSumOfPriceTimesStock(): void
    {
        // 1000×5 + 2000×0 + 500×10 = 10000 centimes
        self::assertSame(10000, $this->repository->sumCatalogueValueCents());
    }

    public function testCountOutOfStock(): void
    {
        self::assertSame(1, $this->repository->countOutOfStock());
    }

    public function testFindLowStockReturnsLowestFirstWithinThreshold(): void
    {
        // seuil 5 → SKU-2 (stock 0) et SKU-1 (stock 5) ; SKU-3 (stock 10) exclu ; trié stock croissant
        $low = $this->repository->findLowStock(5, 10);

        self::assertCount(2, $low);
        self::assertSame('SKU-2', $low[0]->getSku());
        self::assertSame('SKU-1', $low[1]->getSku());
    }

    private function createProduct(string $sku, int $priceCents, int $stock): void
    {
        $product = new Product();
        $product->setSku($sku);
        $product->setName('Produit '.$sku);
        $product->setDescription('Description de test');
        $product->setPriceCents($priceCents);
        $product->setStock($stock);
        $this->em->persist($product);
    }
}
