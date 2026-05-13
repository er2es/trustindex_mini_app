<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\TextNormalizer;
use PHPUnit\Framework\TestCase;

class TextNormalizerTest extends TestCase
{
    public function testLowercasesInput(): void
    {
        self::assertSame('hello world', TextNormalizer::normalize('Hello World'));
    }

    public function testRemovesDiacritics(): void
    {
        self::assertSame('szarny', TextNormalizer::normalize('szárny'));
        self::assertSame('futyul', TextNormalizer::normalize('fütyül'));
        self::assertSame('arulo', TextNormalizer::normalize('Áruló'));
    }

    public function testTrimsWhitespace(): void
    {
        self::assertSame('hello', TextNormalizer::normalize('  hello  '));
    }

    public function testTokenizeSplitsOnSpaces(): void
    {
        self::assertSame(['hello', 'world'], TextNormalizer::tokenize('hello world'));
    }

    public function testTokenizeSplitsOnPunctuation(): void
    {
        $tokens = TextNormalizer::tokenize('hello, world! test.');
        self::assertContains('hello', $tokens);
        self::assertContains('world', $tokens);
        self::assertContains('test', $tokens);
    }

    public function testTokenizeFiltersEmpty(): void
    {
        self::assertSame(['hello', 'world'], TextNormalizer::tokenize('  hello   world  '));
    }
}
