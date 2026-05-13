<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\Table(name: 'company')]
#[ORM\HasLifecycleCallbacks]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $nameNormalized;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'company')]
    private Collection $reviews;

    public function __construct(string $name, string $nameNormalized)
    {
        $this->name = $name;
        $this->nameNormalized = $nameNormalized;
        $this->reviews = new ArrayCollection();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getNameNormalized(): string
    {
        return $this->nameNormalized;
    }

    public function setNameNormalized(string $nameNormalized): void
    {
        $this->nameNormalized = $nameNormalized;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, Review> */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }
}
