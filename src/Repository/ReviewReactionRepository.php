<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Review;
use App\Entity\ReviewReaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReviewReaction>
 */
class ReviewReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReviewReaction::class);
    }

    public function findByReviewAndSession(Review $review, string $sessionId): ?ReviewReaction
    {
        return $this->findOneBy([
            'review' => $review,
            'sessionId' => $sessionId,
        ]);
    }

    public function countByReviewAndType(Review $review, string $type): int
    {
        return (int) $this->createQueryBuilder('rr')
            ->select('COUNT(rr.id)')
            ->where('rr.review = :review')
            ->andWhere('rr.type = :type')
            ->setParameter('review', $review)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
