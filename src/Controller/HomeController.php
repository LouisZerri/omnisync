<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\SyncStatus;
use App\Repository\ChannelRepository;
use App\Repository\ProductRepository;
use App\Repository\SynchronizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    /** Seuil d'alerte de stock faible (rupture incluse). */
    private const LOW_STOCK_THRESHOLD = 5;

    #[Route('/tableau-de-bord', name: 'app_home')]
    public function index(
        ProductRepository $products,
        SynchronizationRepository $synchronizations,
        ChannelRepository $channels,
    ): Response {
        $statusCounts = $synchronizations->countByStatus();
        $done = $statusCounts[SyncStatus::Done->value] ?? 0;
        $failed = $statusCounts[SyncStatus::Failed->value] ?? 0;
        $running = $statusCounts[SyncStatus::Running->value] ?? 0;
        $pending = $statusCounts[SyncStatus::Pending->value] ?? 0;

        // Taux de réussite sur les synchros terminées (done + failed) ; null si aucune.
        $finished = $done + $failed;
        $successRate = $finished > 0 ? (int) round($done / $finished * 100) : null;

        return $this->render('home/index.html.twig', [
            'productCount' => $products->countAll(),
            'catalogueValueCents' => $products->sumCatalogueValueCents(),
            'outOfStockCount' => $products->countOutOfStock(),
            'lowStock' => $products->findLowStock(self::LOW_STOCK_THRESHOLD, 6),
            'syncDone' => $done,
            'syncFailed' => $failed,
            'syncRunning' => $running,
            'syncPending' => $pending,
            'syncTotal' => $done + $failed + $running + $pending,
            'successRate' => $successRate,
            'recentSyncs' => $synchronizations->findRecent(6),
            'channels' => $channels->findBy([], ['name' => 'ASC']),
        ]);
    }
}
