<?php

declare(strict_types=1);

namespace App\Service\Import;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Bundle\DoctrineBundle\Middleware\BacktraceDebugDataHolder;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductCsvImporter
{
    /**
     * En dev, le profiler accumule chaque requête SQL en mémoire. On vide ce collecteur
     * à chaque lot pour ne pas saturer la mémoire sur les gros imports.
     * Câblé uniquement en dev (cf. config/services.yaml) ; reste null en prod.
     */
    private ?BacktraceDebugDataHolder $debugDataHolder = null;

    /**
     * Nombre de lignes traitées avant chaque flush + clear, pour maîtriser la mémoire
     * sur les gros fichiers (traitement par lots).
     */
    private const int BATCH_SIZE = 100;

    /** Colonnes attendues dans l'en-tête du fichier. */
    private const array EXPECTED_HEADERS = ['sku', 'name', 'description', 'price', 'stock'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function setDebugDataHolder(?BacktraceDebugDataHolder $debugDataHolder): void
    {
        $this->debugDataHolder = $debugDataHolder;
    }

    public function import(string $filePath): ImportResult
    {
        $result = new ImportResult();

        $reader = Reader::from($filePath, 'r');
        $reader->setHeaderOffset(0);

        // On vérifie que les colonnes attendues sont bien présentes.
        $missing = array_diff(self::EXPECTED_HEADERS, $reader->getHeader());
        if ([] !== $missing) {
            $result->addError(0, 'Colonnes manquantes : '.implode(', ', $missing));

            return $result;
        }

        $lineNumber = 1; // La ligne 1 est l'en-tête ; les données commencent à la ligne 2.
        $processedInBatch = 0;

        foreach ($reader->getRecords() as $record) {
            ++$lineNumber;

            $this->processRecord($record, $lineNumber, $result);

            // Flush + clear par lots pour ne pas saturer la mémoire.
            if (0 === ++$processedInBatch % self::BATCH_SIZE) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $this->debugDataHolder?->reset();
            }
        }

        // Flush final pour le dernier lot incomplet.
        $this->entityManager->flush();

        return $result;
    }

    /**
     * Traite une ligne : validation, création ou mise à jour.
     *
     * @param array<string, string> $record
     */
    private function processRecord(array $record, int $lineNumber, ImportResult $result): void
    {
        $sku = trim($record['sku']);

        if ('' === $sku) {
            $result->addError($lineNumber, 'La référence (SKU) est vide');

            return;
        }

        // Conversion du prix saisi en euros vers des centimes (entier).
        $priceInput = str_replace(',', '.', trim($record['price']));
        if (!is_numeric($priceInput)) {
            $result->addError($lineNumber, 'Le prix n\'est pas un nombre valide');

            return;
        }
        $priceCents = (int) round((float) $priceInput * 100);

        $stockInput = trim($record['stock']);
        if (!ctype_digit($stockInput)) {
            $result->addError($lineNumber, 'Le stock doit être un entier positif');

            return;
        }

        // Mise à jour si le SKU existe déjà, sinon création.
        $product = $this->productRepository->findOneBy(['sku' => $sku]);
        $isNew = null === $product;
        if ($isNew) {
            $product = new Product();
            $product->setSku($sku);
        }

        $product->setName(trim($record['name']));
        $product->setDescription('' !== trim($record['description']) ? trim($record['description']) : null);
        $product->setPriceCents($priceCents);
        $product->setStock((int) $stockInput);

        // Validation via les contraintes de l'entité avant persistance.
        $violations = $this->validator->validate($product);
        if (count($violations) > 0) {
            $result->addError($lineNumber, (string) $violations->get(0)->getMessage());

            return;
        }

        if ($isNew) {
            $this->entityManager->persist($product);
            $result->addCreated();
        } else {
            $result->addUpdated();
        }
    }
}
