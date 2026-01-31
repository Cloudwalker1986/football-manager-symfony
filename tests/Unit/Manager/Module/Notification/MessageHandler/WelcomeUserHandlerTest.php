<?php

declare(strict_types=1);

namespace App\UnitTests\Manager\Module\Notification\MessageHandler;

use App\Entity\Manager;
use App\Entity\User;
use App\Entity\UserVerification;
use App\Manager\Module\Notification\Message\WelcomeUser;
use App\Manager\Module\Notification\MessageHandler\WelcomeUserHandler;
use App\Repository\Interface\User\UserFinderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(WelcomeUserHandler::class)]
#[Group('unit-tests')]
class WelcomeUserHandlerTest extends TestCase
{
    public function testHandlerSendsEmailAndCreatesVerification(): void
    {
        $userUuid = Uuid::v7();
        $verificationToken = Uuid::v7();
        $user = new User()
            ->setEmailAddress('test@example.com')
            ->setVerification(new UserVerification()->onPrePersistForUuid($verificationToken))
            ->setManager(new Manager()->setName('Test Manager'));

        /** @var UserFinderInterface&MockObject $userFinderInterface */
        $userFinderInterface = $this->createMock(UserFinderInterface::class);
        $userFinderInterface->expects(self::once())
            ->method('byUuid')
            ->with($userUuid)
            ->willReturn($user);

        /** @var MailerInterface&MockObject $mailerInterface */
        $mailerInterface = $this->createMock(MailerInterface::class);
        $mailerInterface->expects(self::once())
            ->method('send')
            ->with($this->isInstanceOf(TemplatedEmail::class));

        /** @var TranslatorInterface&MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())->method('trans')
            ->willReturn('Translated String');

        $handler = new WelcomeUserHandler($userFinderInterface, $mailerInterface, $translator, new NullLogger());

        $handler(new WelcomeUser($userUuid));
    }
}
