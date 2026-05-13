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

    public function countStats(): int
    {
        return (int) $this->getEntityManager()
            ->createQuery('SELECT COUNT(DISTINCT c.id) FROM App\Entity\Review r JOIN r.company c')
            ->getSingleScalarResult();
    }

    /**
     * Company statistics ordered by average rating descending.
     * Pass $perPage > 0 to paginate; omit or pass 0 to return all.
     *
     * @return CompanyStatsDto[]
     */
    public function findStats(int $page = 1, int $perPage = 0): array
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT c.id AS companyId,
                       c.name AS companyName,
                       COUNT(r.id) AS reviewCount,
                       AVG(r.rating) AS averageRating
                FROM App\Entity\Review r
                JOIN r.company c
                GROUP BY c.id, c.name
                ORDER BY averageRating DESC
            ');

        if ($perPage > 0) {
            $query->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage);
        }

        return array_map(
            static fn (array $row) => new CompanyStatsDto(
                companyId: (int) $row['companyId'],
                companyName: $row['companyName'],
                reviewCount: (int) $row['reviewCount'],
                averageRating: min(5.0, max(0.0, round((float) ($row['averageRating'] ?? 0.0), 2))),
            ),
            $query->getResult()
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
