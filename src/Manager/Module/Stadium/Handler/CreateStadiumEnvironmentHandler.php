<?php

declare(strict_types=1);

namespace App\Manager\Module\Stadium\Handler;

use App\Entity\StadiumEnvironment;
use App\Manager\Framework\Command\Interface\CommandHandlerInterface;
use App\Manager\Framework\Command\Interface\CommandInterface;
use App\Manager\Module\Stadium\Command\CreateStadiumEnvironmentCommand;
use App\Repository\ClubRepository;
use App\Repository\StadiumEnvironmentRepository;

readonly class CreateStadiumEnvironmentHandler implements CommandHandlerInterface
{
    public function __construct(
        private ClubRepository $clubRepository,
        private StadiumEnvironmentRepository $stadiumEnvironmentRepository
    ) {
    }

    public function supports(CommandInterface $command): bool
    {
        return $command instanceof CreateStadiumEnvironmentCommand;
    }

    public function handle(CommandInterface $command): void
    {
        /** @var CreateStadiumEnvironmentCommand $command */
        $club = $this->clubRepository->findOneByUuid($command->clubUuid);

        if ($club === null) {
            return;
        }

        $environment = new StadiumEnvironment();
        $environment->setClub($club);

        $this->stadiumEnvironmentRepository->persist($environment);
    }
}
