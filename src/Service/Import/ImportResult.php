<?php

declare(strict_types=1);

namespace App\Service\Import;

/**
 * Bilan d'un import CSV : compteurs et erreurs ligne par ligne.
 */
class ImportResult
{
    private int $created = 0;
    private int $updated = 0;

    /** @var array<int, string> Erreurs indexées par numéro de ligne du fichier. */
    private array $errors = [];

    public function addCreated(): void
    {
        ++$this->created;
    }

    public function addUpdated(): void
    {
        ++$this->updated;
    }

    public function addError(int $line, string $message): void
    {
        $this->errors[$line] = $message;
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    public function getUpdated(): int
    {
        return $this->updated;
    }

    /** @return array<int, string> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return $this->created + $this->updated;
    }

    public function hasErrors(): bool
    {
        return [] !== $this->errors;
    }
}
