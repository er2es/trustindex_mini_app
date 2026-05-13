<?php

declare(strict_types=1);

namespace App\Form\Dto;

use App\Validator\NoProfanity;
use Symfony\Component\Validator\Constraints as Assert;

class ReviewFormDto
{
    #[Assert\NotBlank(message: 'Kérjük, add meg a cégnevet.')]
    #[Assert\Length(max: 255)]
    #[NoProfanity]
    public ?string $companyName = null;

    #[Assert\NotBlank(message: 'Kérjük, válassz értékelést.')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'Az értékelés 1 és 5 között kell legyen.')]
    public ?int $rating = null;

    #[Assert\NotBlank(message: 'Kérjük, írj véleményt.')]
    #[Assert\Length(min: 10, max: 5000, minMessage: 'A vélemény legalább 10 karakter legyen.')]
    #[NoProfanity]
    public ?string $reviewText = null;

    #[Assert\NotBlank(message: 'Kérjük, add meg az e-mail címed.')]
    #[Assert\Email(message: 'Érvényes e-mail cím szükséges.')]
    public ?string $authorEmail = null;
}
