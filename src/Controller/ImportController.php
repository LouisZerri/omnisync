<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Import;
use App\Form\CsvImportType;
use App\Repository\ImportRepository;
use App\Service\Import\ImportLauncher;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/imports', name: 'app_import_')]
class ImportController extends AbstractController
{
    private const int ITEMS_PER_PAGE = 20;

    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ImportLauncher $launcher,
        ImportRepository $importRepository,
        PaginatorInterface $paginator,
    ): Response {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            $launcher->launch($file);

            $this->addFlash('success', 'Import lancé : le traitement se fait en arrière-plan.');

            return $this->redirectToRoute('app_import_index');
        }

        $pagination = $paginator->paginate(
            $importRepository->createListQueryBuilder(),
            $request->query->getInt('page', 1),
            self::ITEMS_PER_PAGE,
        );

        return $this->render('import/index.html.twig', [
            'form' => $form,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Import $import): Response
    {
        return $this->render('import/show.html.twig', [
            'import' => $import,
        ]);
    }
}
