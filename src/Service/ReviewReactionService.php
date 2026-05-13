<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Review;
use App\Entity\ReviewReaction;
use App\Repository\ReviewReactionRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReviewReactionService
{
    public function __construct(
        private readonly ReviewReactionRepository $reactionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Toggle reaction: same type removes it, different type switches it, no existing creates it.
     *
     * @return array{likes: int, dislikes: int, userReaction: string|null}
     */
    public function toggle(Review $review, string $type, string $sessionId): array
    {
        $existing = $this->reactionRepository->findByReviewAndSession($review, $sessionId);

        if (null !== $existing) {
            if ($existing->getType() === $type) {
                $this->entityManager->remove($existing);
            } else {
                $existing->setType($type);
            }
        } else {
            $this->entityManager->persist(new ReviewReaction($review, $type, $sessionId));
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($review);

        $userReaction = null !== $existing && $existing->getType() === $type && $this->entityManager->contains($existing)
            ? null
            : ($existing !== null ? $existing->getType() : $type);

        // Re-query to get fresh counts and actual state
        $afterReaction = $this->reactionRepository->findByReviewAndSession($review, $sessionId);

        return [
            'likes' => $this->reactionRepository->countByReviewAndType($review, ReviewReaction::TYPE_LIKE),
            'dislikes' => $this->reactionRepository->countByReviewAndType($review, ReviewReaction::TYPE_DISLIKE),
            'userReaction' => $afterReaction?->getType(),
        ];
    }
}
