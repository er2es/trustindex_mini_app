<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
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
    public function findByCompany(Company $company, int $page = 1, int $perPage = 0): array
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.company', 'c')
            ->addSelect('c')
            ->where('r.company = :company')
            ->setParameter('company', $company)
            ->orderBy('r.createdAt', 'DESC');

        $this->applyPagination($qb, $page, $perPage);

        return $qb->getQuery()->getResult();
    }

    public function countByCompany(Company $company): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return Review[] */
    public function search(string $query, bool $includeReviewText = false, int $page = 1, int $perPage = 0): array
    {
        $phrase = mb_strtolower(trim($query));
        if ('' === $phrase) {
            return $this->findAllOrderedByDate($page, $perPage);
        }

        $qb = $this->buildSearchQb($phrase, $includeReviewText)
            ->addSelect('c')
            ->orderBy('r.createdAt', 'DESC');

        $this->applyPagination($qb, $page, $perPage);

        return $qb->getQuery()->getResult();
    }

    public function countSearch(string $query, bool $includeReviewText = false): int
    {
        $phrase = mb_strtolower(trim($query));
        if ('' === $phrase) {
            return $this->countAll();
        }

        return (int) $this->buildSearchQb($phrase, $includeReviewText)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function buildSearchQb(string $phrase, bool $includeReviewText): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r')->join('r.company', 'c');

        $companyCondition = 'LOWER(c.name) LIKE :phrase';

        if ($includeReviewText) {
            $qb->andWhere($qb->expr()->orX($companyCondition, 'LOWER(r.reviewText) LIKE :phrase'));
        } else {
            $qb->andWhere($companyCondition);
        }

        return $qb->setParameter('phrase', '%' . $phrase . '%');
    }

    private function applyPagination(QueryBuilder $qb, int $page, int $perPage): void
    {
        if ($perPage <= 0) {
            return;
        }
        $qb->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage);
    }
}
