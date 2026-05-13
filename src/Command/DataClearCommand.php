<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:data:clear', description: 'Törli az összes véleményt, reakciót és céget')]
class DataClearCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$io->confirm('Biztosan törlöd az összes adatot?', false)) {
            $io->warning('Megszakítva.');

            return Command::SUCCESS;
        }

        $conn = $this->em->getConnection();
        $conn->executeStatement('DELETE FROM review_reaction');
        $conn->executeStatement('DELETE FROM review');
        $conn->executeStatement('DELETE FROM company');

        $io->success('Összes adat törölve.');

        return Command::SUCCESS;
    }
}
