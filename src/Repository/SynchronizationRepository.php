<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Synchronization;
use App\Enum\SyncStatus;
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

    /**
     * Nombre de synchronisations par statut (valeur du statut => total).
     *
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        /** @var list<array{status: SyncStatus, total: int}> $rows */
        $rows = $this->createQueryBuilder('s')
            ->select('s.status AS status', 'COUNT(s.id) AS total')
            ->groupBy('s.status')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['status']->value] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * Les dernières synchronisations du journal (produit et canal joints).
     *
     * @return Synchronization[]
     */
    public function findRecent(int $limit): array
    {
        return $this->createListQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
