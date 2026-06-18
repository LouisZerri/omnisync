<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Import;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Import>
 */
class ImportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Import::class);
    }

    /**
     * Listing des imports, le plus récent d'abord.
     */
    public function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.createdAt', 'DESC');
    }
}
