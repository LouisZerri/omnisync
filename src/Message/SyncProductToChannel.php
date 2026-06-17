<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Exécute une ligne de synchronisation (journal). Porte l'id de la Synchronization, qui
 * référence le produit et le canal. Traité de façon asynchrone par un worker.
 */
final readonly class SyncProductToChannel
{
    public function __construct(
        public int $synchronizationId,
    ) {
    }
}
