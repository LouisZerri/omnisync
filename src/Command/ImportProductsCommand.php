<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Import\ProductCsvImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:products:import',
    description: 'Importe un catalogue de produits depuis un fichier CSV',
)]
class ImportProductsCommand extends Command
{
    public function __construct(
        private readonly ProductCsvImporter $importer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Chemin du fichier CSV à importer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = (string) $input->getArgument('file');

        if (!is_file($file) || !is_readable($file)) {
            $io->error(sprintf('Fichier introuvable ou illisible : %s', $file));

            return Command::FAILURE;
        }

        $io->title('Import du catalogue');
        $result = $this->importer->import($file);

        $io->success(sprintf(
            '%d créé(s), %d mis à jour.',
            $result->getCreated(),
            $result->getUpdated(),
        ));

        if ($result->hasErrors()) {
            $io->warning(sprintf('%d ligne(s) en erreur :', count($result->getErrors())));
            $io->listing(array_map(
                static fn (int $line, string $message): string => sprintf('Ligne %d : %s', $line, $message),
                array_keys($result->getErrors()),
                $result->getErrors(),
            ));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
