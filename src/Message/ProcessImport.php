<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Demande de traitement d'un import CSV (le fichier est déjà sauvegardé sur disque).
 * Traité de façon asynchrone par un worker, à partir de l'id de l'entité Import.
 */
final readonly class ProcessImport
{
    public function __construct(
        public int $importId,
    ) {
    }
}
