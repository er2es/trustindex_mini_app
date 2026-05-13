<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\CompanyRepository;
use App\Repository\ReviewRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/companies', name: 'api_company_')]
#[OA\Tag(name: 'Companies')]
class CompanyApiController extends AbstractController
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly ReviewRepository $reviewRepository,
        private readonly int $reviewsPerPage,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(summary: 'Cégstatisztikák listája átlag szerint csökkenő sorrendben')]
    #[OA\Response(response: 200, description: 'Sikeres lekérés')]
    public function list(): JsonResponse
    {
        return $this->json(array_map(static fn ($s) => [
            'companyId' => $s->companyId,
            'companyName' => $s->companyName,
            'reviewCount' => $s->reviewCount,
            'averageRating' => $s->averageRating,
        ], $this->companyRepository->findStats()));
    }

    #[Route('/{id}/reviews', name: 'reviews', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[OA\Get(summary: 'Egy cég véleményei lapozással')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Cég azonosítója')]
    #[OA\Parameter(name: 'page', in: 'query', required: false, description: 'Oldalszám (alapértelmezett: 1)')]
    #[OA\Response(response: 200, description: 'Sikeres lekérés')]
    #[OA\Response(response: 404, description: 'A cég nem található')]
    public function reviews(int $id, Request $request): JsonResponse
    {
        $company = $this->companyRepository->find($id);
        if (null === $company) {
            return $this->json(['error' => 'A cég nem található'], 404);
        }

        $page = max(1, $request->query->getInt('page', 1));
        $total = $this->reviewRepository->countByCompany($company);
        $totalPages = max(1, (int) ceil($total / $this->reviewsPerPage));
        $page = min($page, $totalPages);

        $reviews = $this->reviewRepository->findByCompany($company, $page, $this->reviewsPerPage);

        return $this->json([
            'companyId' => $company->getId(),
            'companyName' => $company->getName(),
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'reviews' => array_map(static fn ($r) => [
                'id' => $r->getId(),
                'rating' => $r->getRating(),
                'reviewText' => $r->getReviewText(),
                'authorEmail' => $r->getAuthorEmail(),
                'likes' => $r->getLikeCount(),
                'dislikes' => $r->getDislikeCount(),
                'createdAt' => $r->getCreatedAt()->format('Y-m-d'),
            ], $reviews),
        ]);
    }

    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET'])]
    #[OA\Get(summary: 'Cégnév autocomplete (min. 3 karakter)')]
    #[OA\Parameter(name: 'q', in: 'query', required: true, description: 'Keresési kifejezés (min. 3 karakter)')]
    #[OA\Response(response: 200, description: 'Egyező cégek listája')]
    public function autocomplete(Request $request): JsonResponse
    {
        $q = trim($request->query->getString('q'));

        if (mb_strlen($q) < 3) {
            return $this->json([]);
        }

        $companies = $this->companyRepository->findForAutocomplete($q);

        return $this->json(array_map(static fn ($c) => [
            'id' => $c->getId(),
            'name' => $c->getName(),
        ], $companies));
    }
}
