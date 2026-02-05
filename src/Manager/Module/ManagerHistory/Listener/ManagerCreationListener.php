<?php

declare(strict_types=1);

namespace App\Manager\Module\ManagerHistory\Listener;


use App\Entity\ManagerHistory;
use App\Manager\Module\User\Event\UserRegistered;
use App\Repository\Interface\CreateEntityInterface;
use App\Repository\Interface\User\UserFinderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserRegistered::class, method: 'registrationCompleted', priority: 0)]
class ManagerCreationListener
{
    public function __construct(
        private UserFinderInterface $finder,
        private CreateEntityInterface $managerHistoryRepository,
        private LoggerInterface $logger
    ) {
    }

    public function registrationCompleted(UserRegistered $event): void
    {
        $user = $this->finder->byUuid($event->userUuid);

        if (!$user) {
            $this->logger->error('User not found after registration with UUID: ' .  $event->userUuid);
            return;
        }

        $manager = $user->getManager();
        if (!$manager) {
            $this->logger->error('Manager not found for user with E-Mail Address: ' .  $user->getEmailAddress());
            return;
        }

        $history = new ManagerHistory();
        $history->setManager($manager);
        $history->setMessage('Manager account created');

        $this->managerHistoryRepository
            ->persist($history)
            ->flush();
    }
}
