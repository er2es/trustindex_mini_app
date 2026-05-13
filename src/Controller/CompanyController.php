<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CompanyRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/companies', name: 'company_')]
class CompanyController extends AbstractController
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly ReviewRepository $reviewRepository,
        private readonly int $reviewsPerPage,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('company/index.html.twig', [
            'stats' => $this->companyRepository->findStats(),
        ]);
    }

    #[Route('/{id}/reviews', name: 'reviews', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function reviews(int $id, Request $request): Response
    {
        $company = $this->companyRepository->find($id);

        if (null === $company) {
            throw $this->createNotFoundException('A cég nem található.');
        }

        $page = max(1, $request->query->getInt('page', 1));
        $total = $this->reviewRepository->countByCompany($company);
        $totalPages = max(1, (int) ceil($total / $this->reviewsPerPage));
        $page = min($page, $totalPages);

        $reviews = $this->reviewRepository->findByCompany($company, $page, $this->reviewsPerPage);

        return $this->render('company/reviews.html.twig', [
            'company' => $company,
            'reviews' => $reviews,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }
}
