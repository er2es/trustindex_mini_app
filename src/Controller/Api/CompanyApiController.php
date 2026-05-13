<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/companies', name: 'api_company_')]
class CompanyApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(['message' => 'TODO']);
    }

    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET'])]
    public function autocomplete(): JsonResponse
    {
        return $this->json([]);
    }
}
