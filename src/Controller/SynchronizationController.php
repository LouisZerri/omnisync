<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Synchronization;
use App\Message\SyncProductToChannel;
use App\Repository\SynchronizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/synchronisations', name: 'app_sync_')]
class SynchronizationController extends AbstractController
{
    private const int ITEMS_PER_PAGE = 30;

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        SynchronizationRepository $synchronizationRepository,
        PaginatorInterface $paginator,
    ): Response {
        $pagination = $paginator->paginate(
            $synchronizationRepository->createListQueryBuilder(),
            $request->query->getInt('page', 1),
            self::ITEMS_PER_PAGE,
        );

        return $this->render('synchronization/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}/relancer', name: 'retry', methods: ['POST'])]
    public function retry(
        Request $request,
        Synchronization $synchronization,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
    ): Response {
        if ($this->isCsrfTokenValid('retry_sync_'.$synchronization->getId(), $request->getPayload()->getString('_token'))) {
            // Remise en file immédiate, pour un retour visuel clair en attendant le worker.
            $synchronization->markPending();
            $entityManager->flush();

            $bus->dispatch(new SyncProductToChannel((int) $synchronization->getId()));

            $this->addFlash('success', 'Synchronisation relancée.');
        }

        return $this->redirectToRoute('app_sync_index');
    }
}
