<?php

declare(strict_types=1);

namespace App\Dto;

class CompanyStatsDto
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $companyName,
        public readonly int $reviewCount,
        public readonly float $averageRating,
    ) {
    }
}
