<?php

declare(strict_types=1);

namespace App\Channel\Dto;

use App\Entity\Product;

/**
 * Représentation des données produit envoyées à un canal. Découple les connecteurs de
 * l'entité Doctrine : ils ne manipulent que ce DTO immuable, jamais le Product directement.
 */
final readonly class ProductPayload
{
    public function __construct(
        public string $sku,
        public string $name,
        public ?string $description,
        public int $priceCents,
        public int $stock,
    ) {
    }

    public static function fromProduct(Product $product): self
    {
        return new self(
            sku: (string) $product->getSku(),
            name: (string) $product->getName(),
            description: $product->getDescription(),
            priceCents: (int) $product->getPriceCents(),
            stock: (int) $product->getStock(),
        );
    }
}
