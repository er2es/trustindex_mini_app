<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Review;
use App\Entity\ReviewReaction;
use App\Service\CompanyService;
use App\Service\TextNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:data:load-demo', description: 'Demo vélemények betöltése Faker segítségével')]
class DataLoadDemoCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CompanyService $companyService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('count', 'c', InputOption::VALUE_OPTIONAL, 'Betöltendő vélemények száma', 20);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $count = max(1, (int) $input->getOption('count'));
        $faker = Factory::create('hu_HU');

        $companyNames = [
            'Kovács és Társa Kft.',
            'Budapest Tech Zrt.',
            'Napfény Vendéglő',
            'Prémium Autószerelő',
            'Digital Wave Kft.',
            'Zöld Patika',
            'FastClick Marketing',
        ];

        $companies = array_map(
            fn (string $name) => $this->companyService->findOrCreate($name),
            $companyNames,
        );

        $this->em->flush();

        $reviews = [];
        for ($i = 0; $i < $count; ++$i) {
            $company  = $faker->randomElement($companies);
            $rating   = $faker->numberBetween(1, 5);
            $text     = $faker->realText($faker->numberBetween(80, 400));
            $email    = $faker->safeEmail();
            $review   = new Review($company, $rating, $text, $email);
            $this->em->persist($review);
            $reviews[] = $review;
        }

        $this->em->flush();

        // Néhány random reakció
        foreach ($reviews as $review) {
            $reactionCount = $faker->numberBetween(0, 6);
            $usedSessions  = [];
            for ($j = 0; $j < $reactionCount; ++$j) {
                $sessionId = $faker->uuid();
                if (\in_array($sessionId, $usedSessions, true)) {
                    continue;
                }
                $usedSessions[] = $sessionId;
                $type     = $faker->randomElement([ReviewReaction::TYPE_LIKE, ReviewReaction::TYPE_DISLIKE]);
                $reaction = new ReviewReaction($review, $type, $sessionId);
                $this->em->persist($reaction);
            }
        }

        $this->em->flush();

        $io->success(sprintf('%d vélemény betöltve %d céghez.', $count, \count($companies)));
        return Command::SUCCESS;
    }
}
