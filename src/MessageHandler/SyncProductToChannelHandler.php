<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Channel\Connector\ChannelConnector;
use App\Channel\Dto\ProductPayload;
use App\Message\SyncProductToChannel;
use App\Repository\ChannelRepository;
use App\Repository\ProductRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
final readonly class SyncProductToChannelHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ChannelRepository $channelRepository,
        private ChannelConnector $connector,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SyncProductToChannel $message): void
    {
        $product = $this->productRepository->find($message->productId);
        $channel = $this->channelRepository->find($message->channelId);

        // Produit ou canal disparu depuis la publication : inutile de réessayer.
        if (null === $product || null === $channel) {
            throw new UnrecoverableMessageHandlingException(
                sprintf('Produit #%d ou canal #%d introuvable.', $message->productId, $message->channelId),
            );
        }

        // Canal désactivé entre-temps : on ignore proprement, sans erreur.
        if (!$channel->isActive()) {
            $this->logger->info('Synchronisation ignorée (canal désactivé)', [
                'product' => $product->getSku(),
                'channel' => $channel->getCode(),
            ]);

            return;
        }

        // pushProduct() lève une ChannelException en cas d'échec → déclenche le retry Messenger.
        $this->connector->pushProduct($channel, ProductPayload::fromProduct($product));

        $this->logger->info('Produit synchronisé', [
            'product' => $product->getSku(),
            'channel' => $channel->getCode(),
        ]);
    }
}
