<?php

declare(strict_types=1);

namespace App\Manager\Module\Club\Listener;

use App\Manager\Framework\Command\CommandBus;
use App\Manager\Module\Club\Event\ClubCreated;
use App\Manager\Module\Message\Command\CreateClubMessageCommand;
use App\Manager\Module\Stadium\Command\CreateStadiumCommand;
use App\Manager\Module\Stadium\Command\CreateStadiumEnvironmentCommand;
use App\Repository\ClubRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ClubCreated::class, method: 'onClubCreated')]
final readonly class ClubCreationListener
{
    public function __construct(
        private CommandBus $commandBus,
        private ClubRepository $clubRepository,
    ) {
    }

    public function onClubCreated(ClubCreated $event): void
    {
        $club = $this->clubRepository->findOneByUuid($event->clubUuid);

        if (!$club) {
            return;
        }

        if ($club->getStadiumEnvironment() === null) {
            $this->commandBus->handle(
                new CreateStadiumEnvironmentCommand(
                    $event->clubUuid
                )
            );
        }

        if ($club->getStadium() === null) {
            $this->commandBus->handle(
                new CreateStadiumCommand(
                    $event->clubUuid
                )
            );
        }

        $this->commandBus->handle(new CreateClubMessageCommand($event->clubUuid));

        $this->clubRepository->flush();
    }
}
