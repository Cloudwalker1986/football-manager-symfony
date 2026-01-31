<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Listener;

use App\Manager\Module\Notification\Message\WelcomeUser;
use App\Manager\Module\User\Event\UserRegistered;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener(event: UserRegistered::class, method: 'registrationCompleted', priority: 0)]
readonly class SendUserVerificationEmailListener
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public function registrationCompleted(UserRegistered $event): void
    {
        $this->bus->dispatch(new WelcomeUser($event->userUuid));
    }
}
