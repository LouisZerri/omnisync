<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Product;
use App\Service\Sync\ProductSyncDispatcher;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Déclenche automatiquement la synchronisation d'un produit dès qu'il est créé ou modifié.
 * Les produits changés sont collectés pendant le flush, puis synchronisés une fois la
 * transaction commitée (postFlush) : aucun message n'est envoyé si le flush échoue.
 */
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postFlush)]
class ProductSyncSubscriber
{
    /** @var array<int, Product> Produits à synchroniser au prochain postFlush, indexés par id. */
    private array $pending = [];

    private bool $enabled = true;

    public function __construct(
        private readonly ProductSyncDispatcher $dispatcher,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->collect($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->collect($args->getObject());
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if (!$this->enabled || [] === $this->pending) {
            return;
        }

        $products = $this->pending;
        $this->pending = [];

        foreach ($products as $product) {
            $this->dispatcher->dispatch($product);
        }
    }

    /**
     * Suspend l'auto-synchronisation (utilisé pendant l'import CSV en masse pour ne pas
     * inonder le broker : la synchro des produits importés est gérée séparément).
     */
    public function disable(): void
    {
        $this->enabled = false;
        $this->pending = [];
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    private function collect(object $entity): void
    {
        if ($this->enabled && $entity instanceof Product) {
            $this->pending[(int) $entity->getId()] = $entity;
        }
    }
}
