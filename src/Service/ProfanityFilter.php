<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

class ProfanityFilter
{
    /** @var string[] Fully normalized (accent-insensitive) banned words */
    private readonly array $bannedNormalized;

    /** @var string[] Lowercase-only (accent-sensitive) banned words, marked with * in config */
    private readonly array $bannedExact;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        string $projectDir,
    ) {
        [$this->bannedNormalized, $this->bannedExact] = $this->loadBannedWords($projectDir);
    }

    /**
     * Returns true if any token in $text matches a banned word.
     * Default: case- and accent-insensitive.
     * Words marked with * in config: case-insensitive only (accent-sensitive).
     */
    public function containsProfanity(string $text): bool
    {
        foreach (TextNormalizer::tokenize($text) as $token) {
            if (\in_array(TextNormalizer::normalize($token), $this->bannedNormalized, true)) {
                return true;
            }
            if (\in_array(mb_strtolower($token), $this->bannedExact, true)) {
                return true;
            }
        }

        return false;
    }

    /** @return array{string[], string[]} [$normalized, $exact] */
    private function loadBannedWords(string $projectDir): array
    {
        $config = Yaml::parseFile($projectDir . '/config/stopwords.yaml');
        $sections = $config['app']['profanity'] ?? [];

        $normalized = [];
        $exact = [];

        foreach ($sections as $sectionWords) {
            foreach ($sectionWords as $word) {
                $word = (string) $word;
                if (str_ends_with($word, '*')) {
                    $exact[] = mb_strtolower(rtrim($word, '*'));
                } else {
                    $normalized[] = TextNormalizer::normalize($word);
                }
            }
        }

        return [array_values(array_unique($normalized)), array_values(array_unique($exact))];
    }
}
