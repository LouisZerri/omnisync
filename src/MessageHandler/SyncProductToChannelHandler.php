<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Channel\Connector\ChannelConnector;
use App\Channel\Dto\ProductPayload;
use App\Channel\Exception\ChannelException;
use App\Message\SyncProductToChannel;
use App\Repository\SynchronizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

/**
 * Exécute une ligne de synchronisation : pousse le produit vers le canal et met à jour le
 * statut du journal (en cours → terminé / échec). En cas d'échec, l'exception est relevée
 * pour déclencher le retry Messenger.
 */
#[AsMessageHandler]
final readonly class SyncProductToChannelHandler
{
    public function __construct(
        private SynchronizationRepository $synchronizationRepository,
        private ChannelConnector $connector,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SyncProductToChannel $message): void
    {
        $synchronization = $this->synchronizationRepository->find($message->synchronizationId);
        if (null === $synchronization) {
            throw new UnrecoverableMessageHandlingException(sprintf('Synchronisation #%d introuvable.', $message->synchronizationId));
        }

        $channel = $synchronization->getChannel();

        // Canal désactivé depuis la mise en file : on n'envoie rien.
        if (!$channel->isActive()) {
            $synchronization->markFailed('Canal désactivé.');
            $this->entityManager->flush();

            return;
        }

        $synchronization->markRunning();
        $this->entityManager->flush();

        try {
            $this->connector->pushProduct($channel, ProductPayload::fromProduct($synchronization->getProduct()));
        } catch (ChannelException $e) {
            $synchronization->markFailed($e->getMessage());
            $this->entityManager->flush();

            throw $e;
        }

        $synchronization->markDone();
        $this->entityManager->flush();

        $this->logger->info('Produit synchronisé', [
            'product' => $synchronization->getProduct()->getSku(),
            'channel' => $channel->getCode(),
        ]);
    }
}
