<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Review;
use App\Form\Dto\ReviewFormDto;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use App\Service\CompanyService;
use App\Service\FormErrorExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/review', name: 'review_')]
class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly CompanyService $companyService,
        private readonly EntityManagerInterface $entityManager,
        private readonly FormErrorExtractor $formErrorExtractor,
        private readonly int $reviewsPerPage,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $query = trim($request->query->getString('q'));
        $includeText = $request->query->getBoolean('includeText', false);
        $page = max(1, $request->query->getInt('page', 1));

        // Minimum 2 karakter szükséges a kereséshez
        $validQuery = \strlen($query) >= 2 ? $query : '';

        $total = '' !== $validQuery
            ? $this->reviewRepository->countSearch($validQuery, $includeText)
            : $this->reviewRepository->countAll();

        $totalPages = max(1, (int) ceil($total / $this->reviewsPerPage));
        $page = min($page, $totalPages);

        $reviews = '' !== $validQuery
            ? $this->reviewRepository->search($validQuery, $includeText, $page, $this->reviewsPerPage)
            : $this->reviewRepository->findAllOrderedByDate($page, $this->reviewsPerPage);

        $paginationVars = [
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'query' => $validQuery,
            'includeText' => $includeText,
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'html' => $this->renderView('review/_list.html.twig', array_merge(
                    ['reviews' => $reviews],
                    $paginationVars,
                )),
                'count' => $total,
                'page' => $page,
                'totalPages' => $totalPages,
            ]);
        }

        return $this->render('review/index.html.twig', array_merge(
            ['reviews' => $reviews, 'includeText' => $includeText],
            $paginationVars,
        ));
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $dto = new ReviewFormDto();
        $form = $this->createForm(ReviewType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($dto->companyId !== null) {
                    $company = $this->companyService->findById($dto->companyId)
                        ?? $this->companyService->findOrCreate((string) $dto->companyName);
                } else {
                    $company = $this->companyService->findOrCreate((string) $dto->companyName);
                }
                $review = new Review($company, (int) $dto->rating, (string) $dto->reviewText, (string) $dto->authorEmail);

                $this->entityManager->persist($review);
                $this->entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Köszönjük a véleményed!',
                        'redirectUrl' => $this->generateUrl('review_index'),
                    ]);
                }

                $this->addFlash('success', 'Köszönjük a véleményed!');

                return $this->redirectToRoute('review_index');
            }

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => $this->formErrorExtractor->extract($form),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        return $this->render('review/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $review = $this->reviewRepository->find($id);

        if (null === $review) {
            throw $this->createNotFoundException('A vélemény nem található.');
        }

        return $this->render('review/show.html.twig', [
            'review' => $review,
        ]);
    }
}
