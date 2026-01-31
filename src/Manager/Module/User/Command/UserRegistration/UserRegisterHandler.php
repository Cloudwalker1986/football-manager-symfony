<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Command\UserRegistration;

use App\Entity\Manager;
use App\Entity\User;
use App\Entity\UserVerification;
use App\Manager\Framework\Command\Interface\CommandHandlerInterface;
use App\Manager\Framework\Command\Interface\CommandInterface;
use App\Manager\Module\User\Enum\Status;
use App\Manager\Module\User\Event\UserRegistered;
use App\Repository\Interface\CreateEntityInterface;
use App\Repository\UserRepository;
use App\Repository\UserVerificationRepository;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Uid\Uuid;

readonly class UserRegisterHandler implements CommandHandlerInterface
{
    public function __construct(
        #[Target(UserRepository::class)]
        private CreateEntityInterface $userRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {

    }

    public function supports(CommandInterface $command): bool
    {
        return get_class($command) === UserRegisterCommand::class;
    }

    public function handle(CommandInterface|UserRegisterCommand $command): void
    {
        $user = new User()
            ->setEmailAddress($command->getEmail())
            ->setPassword($command->getPassword())
            ->setStatus(Status::NOT_VERIFIED)
            ->setVerification(new UserVerification()->setExpiresAt(new \DateTimeImmutable('+ 24 Hours')))
            ->setManager(new Manager()->setName($command->getManagerName()));

        $this->userRepository->persist($user)->flush();

        if ($user->getUuid() instanceof Uuid) {
            $this->eventDispatcher->dispatch(
                new UserRegistered($user->getUuid())
            );
        }
    }
}
