<?php

declare(strict_types=1);

namespace App\Service\Sync;

use App\Entity\Product;
use App\Message\SyncProductToChannel;
use App\Repository\ChannelRepository;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Publie la synchronisation d'un produit vers les canaux actifs (un message par canal).
 * Réutilisé par la commande CLI et par l'action « Synchroniser » du contrôleur.
 */
class ProductSyncDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ChannelRepository $channelRepository,
    ) {
    }

    /**
     * @return int nombre de canaux ciblés
     */
    public function dispatch(Product $product): int
    {
        $channels = $this->channelRepository->findBy(['isActive' => true]);

        foreach ($channels as $channel) {
            $this->bus->dispatch(new SyncProductToChannel((int) $product->getId(), (int) $channel->getId()));
        }

        return \count($channels);
    }
}
