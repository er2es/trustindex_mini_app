<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    /**
     * Full list ordered by newest first, company eager-loaded.
     *
     * @return Review[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.company', 'c')
            ->addSelect('c')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Multi-token search across company name and optionally review text.
     * Each token must appear somewhere (AND logic). No stopword filtering on search.
     *
     * @return Review[]
     */
    public function search(string $query, bool $includeReviewText = false): array
    {
        $tokens = $this->tokenize($query);
        if ([] === $tokens) {
            return $this->findAllOrderedByDate();
        }

        $qb = $this->createQueryBuilder('r')
            ->join('r.company', 'c')
            ->addSelect('c')
            ->orderBy('r.createdAt', 'DESC');

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

        return $qb->getQuery()->getResult();
    }

    /** @return string[] */
    private function tokenize(string $query): array
    {
        $normalized = mb_strtolower(trim($query));
        $tokens = preg_split('/\s+/', $normalized, -1, \PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter($tokens ?? [], static fn (string $t) => mb_strlen($t) >= 2));
    }
}
