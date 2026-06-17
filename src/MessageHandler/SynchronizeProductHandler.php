<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Synchronization;
use App\Message\SynchronizeProduct;
use App\Message\SyncProductToChannel;
use App\Repository\ChannelRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Fan-out : crée une ligne de journal (Synchronization « en file ») par canal actif,
 * puis publie le message de push correspondant. Exécuté dans un worker (flush autorisé).
 */
#[AsMessageHandler]
final readonly class SynchronizeProductHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ChannelRepository $channelRepository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(SynchronizeProduct $message): void
    {
        $product = $this->productRepository->find($message->productId);
        if (null === $product) {
            throw new UnrecoverableMessageHandlingException(sprintf('Produit #%d introuvable.', $message->productId));
        }

        $synchronizations = [];
        foreach ($this->channelRepository->findBy(['isActive' => true]) as $channel) {
            $synchronization = new Synchronization($product, $channel);
            $this->entityManager->persist($synchronization);
            $synchronizations[] = $synchronization;
        }
        $this->entityManager->flush();

        foreach ($synchronizations as $synchronization) {
            $this->bus->dispatch(new SyncProductToChannel((int) $synchronization->getId()));
        }
    }
}
