<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ReviewReactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewReactionRepository::class)]
#[ORM\Table(name: 'review_reaction')]
#[ORM\UniqueConstraint(name: 'uniq_reaction_session', columns: ['review_id', 'session_id'])]
#[ORM\HasLifecycleCallbacks]
class ReviewReaction
{
    public const TYPE_LIKE = 'like';
    public const TYPE_DISLIKE = 'dislike';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Review::class, inversedBy: 'reactions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Review $review;

    #[ORM\Column(type: 'string', length: 10)]
    private string $type;

    #[ORM\Column(type: 'string', length: 128)]
    private string $sessionId;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(Review $review, string $type, string $sessionId)
    {
        $this->review = $review;
        $this->type = $type;
        $this->sessionId = $sessionId;
    }

    #[ORM\PrePersist]
    public function initTimestamps(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReview(): Review
    {
        return $this->review;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
