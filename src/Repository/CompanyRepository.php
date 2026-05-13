<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\CompanyStatsDto;
use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function findByNameNormalized(string $nameNormalized): ?Company
    {
        return $this->findOneBy(['nameNormalized' => $nameNormalized]);
    }

    /**
     * Autocomplete: returns companies whose normalized name contains all query tokens.
     * Minimum 3 characters required (enforced by caller).
     *
     * @return Company[]
     */
    public function findForAutocomplete(string $query, int $limit = 8): array
    {
        $tokens = $this->tokenize($query);
        if ([] === $tokens) {
            return [];
        }

        $qb = $this->createQueryBuilder('c');

        foreach ($tokens as $i => $token) {
            $qb->andWhere("c.nameNormalized LIKE :t{$i}")
                ->setParameter("t{$i}", '%' . $token . '%');
        }

        return $qb
            ->orderBy('c.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Company statistics ordered by average rating descending.
     *
     * @return CompanyStatsDto[]
     */
    public function findStats(): array
    {
        $rows = $this->getEntityManager()
            ->createQuery('
                SELECT c.name AS companyName,
                       COUNT(r.id) AS reviewCount,
                       AVG(r.rating) AS averageRating
                FROM App\Entity\Review r
                JOIN r.company c
                GROUP BY c.id, c.name
                ORDER BY averageRating DESC
            ')
            ->getResult();

        return array_map(
            static fn (array $row) => new CompanyStatsDto(
                companyName: $row['companyName'],
                reviewCount: (int) $row['reviewCount'],
                averageRating: round((float) $row['averageRating'], 2),
            ),
            $rows
        );
    }

    /** @return string[] */
    private function tokenize(string $query): array
    {
        $normalized = mb_strtolower(trim($query));
        $tokens = preg_split('/\s+/', $normalized, -1, \PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter($tokens ?? [], static fn (string $t) => mb_strlen($t) >= 2));
    }
}
