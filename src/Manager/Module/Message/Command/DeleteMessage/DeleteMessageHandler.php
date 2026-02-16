<?php

declare(strict_types=1);

namespace App\Manager\Module\Message\Command\DeleteMessage;

use App\Manager\Framework\Command\Interface\CommandHandlerInterface;
use App\Manager\Framework\Command\Interface\CommandInterface;
use App\Repository\Interface\DeleteEntityInterface;
use App\Repository\Interface\Message\MessageFinderInterface;
use App\Repository\MessageRepository;
use Symfony\Component\Uid\Uuid;

readonly class DeleteMessageHandler implements CommandHandlerInterface
{
    public function __construct(
        private MessageFinderInterface $messageFinder,
        private DeleteEntityInterface $messageDeleteRepository
    ) {
    }

    public function supports(CommandInterface $command): bool
    {
        return $command instanceof DeleteMessageCommand;
    }

    public function handle(CommandInterface $command): void
    {
        /** @var DeleteMessageCommand $command */
        $message = $this->messageFinder->byUuid(Uuid::fromString($command->getUuid()));

        if (null !== $message) {
            $this->messageDeleteRepository->delete($message);
            $this->messageDeleteRepository->flush();
        }
    }
}
