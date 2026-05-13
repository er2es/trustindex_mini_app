<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;

class CompanyService
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Finds an existing company by normalized name or creates a new one.
     * The canonical name is taken from the first submission (original casing preserved).
     */
    public function findOrCreate(string $name): Company
    {
        $normalized = TextNormalizer::normalize($name);
        $company = $this->companyRepository->findByNameNormalized($normalized);

        if (null === $company) {
            $company = new Company(trim($name), $normalized);
            $this->entityManager->persist($company);
        }

        return $company;
    }
}
