<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\CompanyRepository;
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
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(summary: 'Cégstatisztikák listája átlag szerint csökkenő sorrendben')]
    #[OA\Response(response: 200, description: 'Sikeres lekérés')]
    public function list(): JsonResponse
    {
        return $this->json(array_map(static fn ($s) => [
            'companyName' => $s->companyName,
            'reviewCount' => $s->reviewCount,
            'averageRating' => $s->averageRating,
        ], $this->companyRepository->findStats()));
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
