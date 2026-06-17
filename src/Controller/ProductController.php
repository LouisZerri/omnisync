<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Form\CsvImportType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\Import\ProductCsvImporter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produits', name: 'app_product_')]
class ProductController extends AbstractController
{
    private const int ITEMS_PER_PAGE = 20;

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        PaginatorInterface $paginator,
    ): Response {
        $pagination = $paginator->paginate(
            $productRepository->createListQueryBuilder(),
            $request->query->getInt('page', 1),
            self::ITEMS_PER_PAGE,
        );

        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été créé.');

            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été modifié.');

            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form,
            'product' => $product,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_product_'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été supprimé.');
        }

        return $this->redirectToRoute('app_product_index');
    }

    #[Route('/importer', name: 'import', methods: ['GET', 'POST'])]
    public function import(Request $request, ProductCsvImporter $importer): Response
    {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $form->get('file')->getData();

            // Marge mémoire pour les gros imports (en prod : pas de profiler, donc largement suffisant).
            // Pour de très gros fichiers, préférer la commande « app:products:import ».
            ini_set('memory_limit', '512M');

            $result = $importer->import($file->getPathname());

            return $this->render('product/import_result.html.twig', [
                'result' => $result,
            ]);
        }

        return $this->render('product/import.html.twig', [
            'form' => $form,
        ]);
    }
}
