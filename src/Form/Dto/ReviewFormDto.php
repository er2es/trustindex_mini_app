<?php

declare(strict_types=1);

namespace App\Form\Dto;

use App\Validator\NoProfanity;
use App\Validator\SmtpSafeEmail;
use Symfony\Component\Validator\Constraints as Assert;

class ReviewFormDto
{
    #[Assert\Length(max: 255)]
    #[NoProfanity]
    public ?string $companyName = null;

    #[Assert\NotBlank(message: 'Kérjük, válassz értékelést.')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'Az értékelés 1 és 5 között kell legyen.')]
    public ?int $rating = null;

    #[Assert\NotBlank(message: 'Kérjük, írj véleményt.')]
    #[Assert\Length(min: 10, max: 1000, minMessage: 'A vélemény legalább 10 karakter legyen.', maxMessage: 'A vélemény legfeljebb 1000 karakter lehet.')]
    #[NoProfanity]
    public ?string $reviewText = null;

    #[Assert\NotBlank(message: 'Kérjük, add meg az e-mail címed.')]
    #[SmtpSafeEmail]
    public ?string $authorEmail = null;
}
