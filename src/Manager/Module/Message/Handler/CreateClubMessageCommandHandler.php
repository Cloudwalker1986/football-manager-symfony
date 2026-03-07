<?php

declare(strict_types=1);

namespace App\Manager\Module\Message\Handler;

use App\Entity\Message;
use App\Manager\Framework\Command\Interface\CommandHandlerInterface;
use App\Manager\Framework\Command\Interface\CommandInterface;
use App\Manager\Module\Message\Command\CreateClubMessageCommand;
use App\Repository\ClubRepository;
use App\Repository\MessageRepository;

readonly class CreateClubMessageCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private MessageRepository $messageRepository,
        private ClubRepository $clubRepository
    ) {
    }

    public function supports(CommandInterface $command): bool
    {
        return $command instanceof CreateClubMessageCommand;
    }

    public function handle(CommandInterface $command): void
    {
        /** @var CreateClubMessageCommand $command */
        $club = $this->clubRepository->findOneByUuid($command->clubUuid);

        if ($club === null) {
            return;
        }

        $welcomeMessage = new Message();
        $welcomeMessage->setManager($club->getManager());
        $welcomeMessage->setSubject('wizard.club.creation.message.subject');
        $welcomeMessage->setMessage('wizard.club.creation.message.body');
        $welcomeMessage->setParameters(['%club_name%' => $club->getName()]);

        $this->messageRepository->persist($welcomeMessage);
    }
}
