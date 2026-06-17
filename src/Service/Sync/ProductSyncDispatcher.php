<?php

declare(strict_types=1);

namespace App\Service\Sync;

use App\Entity\Product;
use App\Message\SynchronizeProduct;
use App\Repository\ChannelRepository;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Déclenche la synchronisation d'un produit. Se contente de publier un message léger
 * (aucune écriture en base) : il est donc sûr de l'appeler depuis un événement Doctrine.
 * Le fan-out par canal + la création du journal sont faits par le worker.
 */
class ProductSyncDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ChannelRepository $channelRepository,
    ) {
    }

    /**
     * @return int nombre de canaux actifs qui seront ciblés
     */
    public function dispatch(Product $product): int
    {
        $this->bus->dispatch(new SynchronizeProduct((int) $product->getId()));

        return $this->channelRepository->count(['isActive' => true]);
    }
}
