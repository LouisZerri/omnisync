<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Demande de synchronisation d'un produit vers un canal. Porte des identifiants
 * (pas des entités) : le message est sérialisé et traité de façon asynchrone par un worker.
 */
final readonly class SyncProductToChannel
{
    public function __construct(
        public int $productId,
        public int $channelId,
    ) {
    }
}
