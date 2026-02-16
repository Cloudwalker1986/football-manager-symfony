<?php

declare(strict_types=1);

namespace App\Command;

use App\Manager\Framework\Command\CommandBus;
use App\Manager\Module\Message\Command\DeleteMessage\DeleteMessageCommand;
use App\Repository\Interface\Message\MessageFinderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cleanup:messages',
    description: 'Deletes messages older than 90 days.',
)]
class CleanupMessagesCommand extends Command
{
    public function __construct(
        private readonly MessageFinderInterface $messageFinder,
        private readonly CommandBus $commandBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $ninetyDaysAgo = new \DateTimeImmutable('-90 days');

        $io->info(sprintf('Searching for messages older than %s...', $ninetyDaysAgo->format('Y-m-d H:i:s')));

        $oldMessages = $this->messageFinder->findOlderThan($ninetyDaysAgo);
        $count = count($oldMessages);

        if ($count === 0) {
            $io->success('No old messages found.');
            return Command::SUCCESS;
        }

        $io->progressStart($count);

        foreach ($oldMessages as $message) {
            $uuid = $message->getUuid();
            if ($uuid !== null) {
                $this->commandBus->handle(new DeleteMessageCommand($uuid->toString()));
            }
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success(sprintf('Successfully deleted %d old message(s).', $count));

        return Command::SUCCESS;
    }
}
