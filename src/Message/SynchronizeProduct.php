<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Demande de synchronisation d'un produit vers tous les canaux actifs. Traité par un worker
 * qui crée une ligne de journal (Synchronization) par canal puis publie le push correspondant.
 */
final readonly class SynchronizeProduct
{
    public function __construct(
        public int $productId,
    ) {
    }
}
