<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SynchronizationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
}
