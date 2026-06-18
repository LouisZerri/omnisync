<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ImportStatus;
use App\Repository\ImportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Suit un job d'import CSV traité de façon asynchrone (statut, compteurs, rapport d'erreurs).
 */
#[ORM\Entity(repositoryClass: ImportRepository::class)]
class Import
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $originalFilename;

    /** Chemin du fichier uploadé sauvegardé sur disque, lu par le worker. */
    #[ORM\Column(length: 255)]
    private string $storedPath;

    #[ORM\Column(enumType: ImportStatus::class)]
    private ImportStatus $status;

    #[ORM\Column]
    private int $createdCount = 0;

    #[ORM\Column]
    private int $updatedCount = 0;

    /**
     * Erreurs ligne par ligne (numéro de ligne => message).
     *
     * @var array<int, string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $errors = [];

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $originalFilename, string $storedPath)
    {
        $this->originalFilename = $originalFilename;
        $this->storedPath = $storedPath;
        $this->status = ImportStatus::Pending;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function markRunning(): void
    {
        $this->status = ImportStatus::Running;
        $this->touch();
    }

    /**
     * @param array<int, string> $errors
     */
    public function complete(int $createdCount, int $updatedCount, array $errors): void
    {
        $this->status = ImportStatus::Done;
        $this->createdCount = $createdCount;
        $this->updatedCount = $updatedCount;
        $this->errors = $errors;
        $this->touch();
    }

    public function fail(string $message): void
    {
        $this->status = ImportStatus::Failed;
        $this->errors = [0 => $message];
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

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function getStoredPath(): string
    {
        return $this->storedPath;
    }

    public function getStatus(): ImportStatus
    {
        return $this->status;
    }

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    /**
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorCount(): int
    {
        return \count($this->errors);
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
