<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Channel;
use App\Entity\Product;
use App\Enum\SyncStatus;
use App\Message\SynchronizeProduct;
use App\Message\SyncProductToChannel;
use App\MessageHandler\SynchronizeProductHandler;
use App\Repository\ChannelRepository;
use App\Repository\ProductRepository;
use App\Repository\SynchronizationRepository;
use App\Tests\IntegrationTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Vérifie le cœur de la synchro : le fan-out crée une ligne de journal « en file » par
 * canal ACTIF (les canaux inactifs sont ignorés) et publie un message de push par ligne.
 */
final class SynchronizeProductHandlerTest extends IntegrationTestCase
{
    public function testFanOutTargetsActiveChannelsOnly(): void
    {
        $product = $this->createProduct();
        $this->createChannel('Voltura', 'voltura', true);
        $this->createChannel('Cartelio', 'cartelio', true);
        $this->createChannel('Zelmark', 'zelmark', false); // inactif → doit être ignoré
        $this->em->flush();

        // Bus espion : enregistre les messages dispatchés sans les exécuter.
        $bus = new class implements MessageBusInterface {
            /** @var list<object> */
            public array $dispatched = [];

            public function dispatch(object $message, array $stamps = []): Envelope
            {
                $this->dispatched[] = $message;

                return new Envelope($message);
            }
        };

        $handler = new SynchronizeProductHandler(
            self::getContainer()->get(ProductRepository::class),
            self::getContainer()->get(ChannelRepository::class),
            $this->em,
            $bus,
        );

        $handler(new SynchronizeProduct((int) $product->getId()));

        // 2 canaux actifs → 2 lignes de journal « en file », le canal inactif est ignoré.
        $synchronizations = self::getContainer()->get(SynchronizationRepository::class)->findAll();
        self::assertCount(2, $synchronizations);
        foreach ($synchronizations as $synchronization) {
            self::assertSame(SyncStatus::Pending, $synchronization->getStatus());
            self::assertTrue($synchronization->getChannel()->isActive());
        }

        // Un message de push dispatché par ligne de journal.
        self::assertCount(2, $bus->dispatched);
        foreach ($bus->dispatched as $message) {
            self::assertInstanceOf(SyncProductToChannel::class, $message);
        }
    }

    private function createProduct(): Product
    {
        $product = new Product();
        $product->setSku('SKU-TEST');
        $product->setName('Produit test');
        $product->setDescription('Description de test');
        $product->setPriceCents(1000);
        $product->setStock(5);
        $this->em->persist($product);

        return $product;
    }

    private function createChannel(string $name, string $code, bool $active): void
    {
        $channel = new Channel();
        $channel->setName($name);
        $channel->setCode($code);
        $channel->setIsActive($active);
        $this->em->persist($channel);
    }
}
