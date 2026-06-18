<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ProcessImport;
use App\Repository\ImportRepository;
use App\Service\Import\ProductCsvImporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

/**
 * Traite un import CSV dans un worker : marque l'Import « en cours », lance l'importer
 * existant sur le fichier sauvegardé, puis enregistre le bilan (compteurs + erreurs).
 */
#[AsMessageHandler]
final readonly class ProcessImportHandler
{
    public function __construct(
        private ImportRepository $importRepository,
        private ProductCsvImporter $importer,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(ProcessImport $message): void
    {
        $import = $this->importRepository->find($message->importId);
        if (null === $import) {
            throw new UnrecoverableMessageHandlingException(sprintf('Import #%d introuvable.', $message->importId));
        }

        $import->markRunning();
        $this->entityManager->flush();
        $storedPath = $import->getStoredPath();

        try {
            $result = $this->importer->import($storedPath);
        } catch (\Throwable $e) {
            // L'importer vide l'EntityManager (mémoire) : on recharge l'Import pour le marquer en échec.
            $this->importRepository->find($message->importId)?->fail($e->getMessage());
            $this->entityManager->flush();
            @unlink($storedPath);

            return;
        }

        // L'EntityManager a été vidé par l'importer : on recharge l'entité avant d'écrire le bilan.
        $this->importRepository->find($message->importId)?->complete(
            $result->getCreated(),
            $result->getUpdated(),
            $result->getErrors(),
        );
        $this->entityManager->flush();

        @unlink($storedPath);
    }
}
