<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\SyncStatus;
use App\Repository\SynchronizationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trace une tentative de synchronisation d'un produit vers un canal (le journal de synchro).
 */
#[ORM\Entity(repositoryClass: SynchronizationRepository::class)]
class Synchronization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Channel $channel;

    #[ORM\Column(enumType: SyncStatus::class)]
    private SyncStatus $status;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $error = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(Product $product, Channel $channel)
    {
        $this->product = $product;
        $this->channel = $channel;
        $this->status = SyncStatus::Pending;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function markPending(): void
    {
        $this->status = SyncStatus::Pending;
        $this->error = null;
        $this->touch();
    }

    public function markRunning(): void
    {
        $this->status = SyncStatus::Running;
        $this->error = null;
        $this->touch();
    }

    public function markDone(): void
    {
        $this->status = SyncStatus::Done;
        $this->error = null;
        $this->touch();
    }

    public function markFailed(string $error): void
    {
        $this->status = SyncStatus::Failed;
        $this->error = $error;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function getStatus(): SyncStatus
    {
        return $this->status;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
