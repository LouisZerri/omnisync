<?php

declare(strict_types=1);

namespace App\Service\Import;

use App\Entity\Import;
use App\Message\ProcessImport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Démarre un import CSV asynchrone : sauvegarde le fichier uploadé, crée son suivi (Import)
 * et publie le message de traitement. La requête web rend la main immédiatement.
 */
class ImportLauncher
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        #[Autowire('%kernel.project_dir%/var/imports')]
        private readonly string $importDir,
    ) {
    }

    public function launch(UploadedFile $file): Import
    {
        $originalName = $file->getClientOriginalName();
        $storedName = uniqid('import_', true).'.csv';
        $file->move($this->importDir, $storedName);

        $import = new Import($originalName, $this->importDir.'/'.$storedName);
        $this->entityManager->persist($import);
        $this->entityManager->flush();

        $this->bus->dispatch(new ProcessImport((int) $import->getId()));

        return $import;
    }
}
