<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Synchronization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Synchronization>
 */
class SynchronizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Synchronization::class);
    }

    /**
     * Listing du journal, le plus récent d'abord. Joint produit et canal pour éviter le N+1.
     */
    public function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->addSelect('p', 'c')
            ->join('s.product', 'p')
            ->join('s.channel', 'c')
            ->orderBy('s.updatedAt', 'DESC');
    }
}
