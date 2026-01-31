<?php

declare(strict_types=1);

namespace App\UnitTests\Manager\Module\User\Listener;

use App\Manager\Module\Notification\Message\WelcomeUser;
use App\Manager\Module\User\Event\UserRegistered;
use App\Manager\Module\User\Listener\SendUserVerificationEmailListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[CoversClass(SendUserVerificationEmailListener::class)]
#[CoversMethod(SendUserVerificationEmailListener::class, 'registrationCompleted')]
class SendUserVerificationEmailListenerTest extends TestCase
{
    #[Test]
    public function itDispatchesBusMessage(): void
    {
        $uuid = Uuid::v7();

        /** @var MessageBusInterface&MockObject $busMock */
        $busMock = $this->createMock(MessageBusInterface::class);
        $busMock
            ->expects(self::once())
            ->method('dispatch')
            ->with(new WelcomeUser($uuid))
            ->willReturn(new Envelope(new WelcomeUser($uuid)));

        $listener = new SendUserVerificationEmailListener($busMock);

        $listener->registrationCompleted(new UserRegistered($uuid));
    }
}
