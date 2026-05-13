<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /** @return Review[] */
    public function findAllOrderedByDate(int $page = 1, int $perPage = 0): array
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.company', 'c')
            ->addSelect('c')
            ->orderBy('r.createdAt', 'DESC');

        $this->applyPagination($qb, $page, $perPage);

        return $qb->getQuery()->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return Review[] */
    public function search(string $query, bool $includeReviewText = false, int $page = 1, int $perPage = 0): array
    {
        $tokens = $this->tokenize($query);
        if ([] === $tokens) {
            return $this->findAllOrderedByDate($page, $perPage);
        }

        $qb = $this->buildSearchQb($tokens, $includeReviewText)
            ->addSelect('c')
            ->orderBy('r.createdAt', 'DESC');

        $this->applyPagination($qb, $page, $perPage);

        return $qb->getQuery()->getResult();
    }

    public function countSearch(string $query, bool $includeReviewText = false): int
    {
        $tokens = $this->tokenize($query);
        if ([] === $tokens) {
            return $this->countAll();
        }

        return (int) $this->buildSearchQb($tokens, $includeReviewText)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function buildSearchQb(array $tokens, bool $includeReviewText): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r')->join('r.company', 'c');

        foreach ($tokens as $i => $token) {
            $param = "t{$i}";
            $companyCondition = "LOWER(c.name) LIKE :{$param}";
            $textCondition = "LOWER(r.reviewText) LIKE :{$param}";

            if ($includeReviewText) {
                $qb->andWhere($qb->expr()->orX($companyCondition, $textCondition));
            } else {
                $qb->andWhere($companyCondition);
            }

            $qb->setParameter($param, '%' . $token . '%');
        }

        return $qb;
    }

    private function applyPagination(QueryBuilder $qb, int $page, int $perPage): void
    {
        if ($perPage <= 0) {
            return;
        }
        $qb->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage);
    }

    /** @return string[] */
    private function tokenize(string $query): array
    {
        $normalized = mb_strtolower(trim($query));
        $tokens = preg_split('/\s+/', $normalized, -1, \PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter($tokens ?? [], static fn (string $t) => mb_strlen($t) >= 2));
    }
}
