<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

class ProfanityFilter
{
    /** @var string[] Normalized banned words (flat, de-duped) */
    private readonly array $bannedWords;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        string $projectDir,
    ) {
        $this->bannedWords = $this->loadBannedWords($projectDir);
    }

    /**
     * Returns true if any token in $text exactly matches a banned word (whole-word, case- and accent-insensitive).
     */
    public function containsProfanity(string $text): bool
    {
        foreach (TextNormalizer::tokenize($text) as $token) {
            if (\in_array(TextNormalizer::normalize($token), $this->bannedWords, true)) {
                return true;
            }
        }

        return false;
    }

    /** @return string[] */
    private function loadBannedWords(string $projectDir): array
    {
        $config = Yaml::parseFile($projectDir . '/config/stopwords.yaml');
        $sections = $config['app']['profanity'] ?? [];

        $words = [];
        foreach ($sections as $sectionWords) {
            foreach ($sectionWords as $word) {
                $words[] = TextNormalizer::normalize((string) $word);
            }
        }

        return array_values(array_unique($words));
    }
}
