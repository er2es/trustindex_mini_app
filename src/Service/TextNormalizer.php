<?php

declare(strict_types=1);

namespace App\Service;

class TextNormalizer
{
    /**
     * Lowercase + NFD decomposition + strip diacritics + trim.
     * "Szárny" → "szarny", "FÜTYÜL" → "futyul"
     */
    public static function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = \Normalizer::normalize($text, \Normalizer::NFD);

        return (string) preg_replace('/[\x{0300}-\x{036f}]/u', '', $text);
    }

    /**
     * Split text into word tokens, stripping whitespace and punctuation.
     *
     * @return string[]
     */
    public static function tokenize(string $text): array
    {
        $tokens = preg_split('/[\s\p{P}]+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        return $tokens !== false ? array_values($tokens) : [];
    }
}
