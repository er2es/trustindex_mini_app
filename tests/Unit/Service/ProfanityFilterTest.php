<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\ProfanityFilter;
use PHPUnit\Framework\TestCase;

class ProfanityFilterTest extends TestCase
{
    private ProfanityFilter $filter;

    protected function setUp(): void
    {
        // tests/Unit/Service/ → 3 levels up to project root
        $projectDir = \dirname(__DIR__, 3);
        $this->filter = new ProfanityFilter($projectDir);
    }

    public function testCleanTextIsAllowed(): void
    {
        self::assertFalse($this->filter->containsProfanity('Ez egy kiváló cég, nagyon elégedett vagyok.'));
    }

    public function testHungarianProfanityDetected(): void
    {
        self::assertTrue($this->filter->containsProfanity('Ez szar volt teljesen.'));
    }

    public function testEnglishProfanityDetected(): void
    {
        self::assertTrue($this->filter->containsProfanity('This is total bullshit.'));
    }

    public function testCaseInsensitiveDetection(): void
    {
        self::assertTrue($this->filter->containsProfanity('Ez SZAR volt.'));
        self::assertTrue($this->filter->containsProfanity('Ez Szar volt.'));
        self::assertTrue($this->filter->containsProfanity('FUCK this service.'));
    }

    /** Ékezetes variáns is elkapja a szót */
    public function testAccentInsensitiveDetection(): void
    {
        // "szár" normalizálva = "szar" → match
        self::assertTrue($this->filter->containsProfanity('Na ez szár!'));
    }

    /** Szótöredék NEM kap el – "szárny" ≠ "szar" */
    public function testPartialWordIsNotDetected(): void
    {
        self::assertFalse($this->filter->containsProfanity('A madár szárnya gyönyörű volt.'));
    }

    /** Összefűzött szó NEM kap el – "faszom" a listán nincs, "fasz" sem illik rá */
    public function testCompoundWordPartNotDetected(): void
    {
        // "szárnyaló" nem tartalmaz tiltott egész szót
        self::assertFalse($this->filter->containsProfanity('Szárnyaló karriert kívánok!'));
    }

    public function testEmptyTextIsAllowed(): void
    {
        self::assertFalse($this->filter->containsProfanity(''));
    }

    public function testProfanityInsideSentenceDetected(): void
    {
        self::assertTrue($this->filter->containsProfanity('Teljesen kurva rossz ez a cég!'));
    }
}
