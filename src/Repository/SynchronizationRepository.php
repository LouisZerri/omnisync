<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Synchronization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
